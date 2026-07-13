<?php
/**
 * compare.php — مقارنة منتخبين (?a=ARG&b=SUI).
 */
require __DIR__ . '/includes/bootstrap.php';

$ar   = (current_lang() === 'ar');
$L    = fn(string $a, string $e) => $ar ? $a : $e;
$allTeams = DataService::allTeams();
$teamList = array_keys($allTeams);
usort($teamList, fn($x, $y) => strcmp(team_name($x), team_name($y)));

$a = isset($_GET['a']) ? trim((string)$_GET['a']) : '';
$b = isset($_GET['b']) ? trim((string)$_GET['b']) : '';
$validA = $a !== '' && isset($allTeams[$a]);
$validB = $b !== '' && isset($allTeams[$b]);
$ready  = $validA && $validB && $a !== $b;

$page_title = $L('مقارنة المنتخبات', 'Team comparison');
if ($ready) {
    $page_title = team_name($a) . ' ' . t('vs') . ' ' . team_name($b);
}
$page_desc = $ready
    ? $L(
        'مقارنة ' . team_name($a) . ' و' . team_name($b) . ' في كأس العالم 2026 — ترتيب FIFA، سجل البطولة، إحصائيات FIFA، والمواجهات المباشرة على foot-boll.',
        'Compare ' . team_name($a) . ' and ' . team_name($b) . ' at World Cup 2026 — FIFA rank, tournament record, FIFA stats, and head-to-head on foot-boll.'
    )
    : $L(
        'قارن أي منتخبين في مونديال 2026: ترتيب FIFA، الأهداف، الاستحواذ، xG، والمواجهات المباشرة — أداة حصرية على foot-boll.',
        'Compare any two World Cup 2026 teams: FIFA rank, goals, possession, xG, and head-to-head — exclusive on foot-boll.'
    );
$page_keywords = $L('مقارنة منتخبات, foot-boll, كأس العالم 2026', 'team comparison, foot-boll, World Cup 2026');

/** صف مقارنة بقيمتين وأشرطة */
function cmp_row(string $label, $v1, $v2, string $unit = '', bool $lowerBetter = false): void {
    if ($v1 === null && $v2 === null) return;
    $n1 = is_numeric($v1) ? (float)$v1 : 0.0;
    $n2 = is_numeric($v2) ? (float)$v2 : 0.0;
    $sum = $n1 + $n2;
    $p1 = $sum > 0 ? round($n1 / $sum * 100, 1) : 50.0;
    $p2 = 100 - $p1;
    $w1 = $lowerBetter ? ($n1 < $n2) : ($n1 > $n2);
    $w2 = $lowerBetter ? ($n2 < $n1) : ($n2 > $n1);
    if ($n1 === $n2) { $w1 = $w2 = false; }
    $fmt = fn($v) => is_int($v) || (is_float($v) && floor($v) == $v)
        ? (string)(int)$v
        : rtrim(rtrim(number_format((float)$v, 2, '.', ''), '0'), '.');
    ?>
    <div class="cmp-stat">
      <div class="cmp-stat-vals">
        <span class="cmp-val cmp-val-a<?= $w1 ? ' cmp-win' : '' ?>"><?= e($fmt($v1 ?? 0)) ?><?= e($unit) ?></span>
        <span class="cmp-lbl"><?= e($label) ?></span>
        <span class="cmp-val cmp-val-b<?= $w2 ? ' cmp-win' : '' ?>"><?= e($fmt($v2 ?? 0)) ?><?= e($unit) ?></span>
      </div>
      <div class="md-stat-bar">
        <span class="md-stat-bar-1" style="flex:<?= $p1 ?>"></span>
        <span class="md-stat-bar-2" style="flex:<?= $p2 ?>"></span>
      </div>
    </div>
    <?php
}

tpl('header');
?>

<div class="page-head">
  <h1>⚖️ <?= e($L('مقارنة المنتخبات', 'Team comparison')) ?></h1>
  <p class="muted"><?= e($L(
    'قارن أي منتخبين في البطولة — ترتيب FIFA، سجل المباريات، ومتوسطات FIFA التقنية.',
    'Compare any two tournament teams — FIFA rank, match record, and FIFA technical averages.'
  )) ?></p>
</div>

<form class="cmp-picker" method="get" action="<?= e(url('compare.php')) ?>">
  <label>
    <span><?= e($L('المنتخب الأول', 'Team A')) ?></span>
    <select name="a" required>
      <option value=""><?= e($L('اختر منتخباً', 'Select team')) ?></option>
      <?php foreach ($teamList as $code): ?>
        <option value="<?= e($code) ?>"<?= $a === $code ? ' selected' : '' ?>><?= e(team_name($code)) ?></option>
      <?php endforeach; ?>
    </select>
  </label>
  <span class="cmp-vs-pill"><?= e(t('vs')) ?></span>
  <label>
    <span><?= e($L('المنتخب الثاني', 'Team B')) ?></span>
    <select name="b" required>
      <option value=""><?= e($L('اختر منتخباً', 'Select team')) ?></option>
      <?php foreach ($teamList as $code): ?>
        <option value="<?= e($code) ?>"<?= $b === $code ? ' selected' : '' ?>><?= e(team_name($code)) ?></option>
      <?php endforeach; ?>
    </select>
  </label>
  <button type="submit" class="btn btn-accent"><?= e($L('قارن', 'Compare')) ?></button>
</form>

<?php if ($a === $b && $validA): ?>
  <p class="alert"><?= e($L('اختر منتخبين مختلفين.', 'Pick two different teams.')) ?></p>
<?php elseif ($ready):
    $recA = TeamCompare::record($a);
    $recB = TeamCompare::record($b);
    $fifaA = TeamCompare::fifaRow($a);
    $fifaB = TeamCompare::fifaRow($b);
    $h2h   = TeamCompare::headToHead($a, $b);
    $rankA = Rankings::of($a);
    $rankB = Rankings::of($b);
    $grpA  = $allTeams[$a] ?? '';
    $grpB  = $allTeams[$b] ?? '';
    $posA  = TeamCompare::groupRank($a);
    $posB  = TeamCompare::groupRank($b);
?>
<section class="cmp-hero">
  <div class="cmp-team cmp-team-a">
    <?= flag_img($a, 'w160') ?>
    <h2><a href="<?= e(url('team.php', ['team' => $a])) ?>"><?= e(team_name($a)) ?></a></h2>
    <?php if ($rankA): ?><span class="cmp-meta"><?= e(t('fifa_rank')) ?> #<?= (int)$rankA ?></span><?php endif; ?>
    <?php if ($grpA): ?><span class="cmp-meta"><?= e(group_label($grpA)) ?><?= $posA ? ' · #' . (int)$posA : '' ?></span><?php endif; ?>
  </div>
  <div class="cmp-mid">⚖️</div>
  <div class="cmp-team cmp-team-b">
    <?= flag_img($b, 'w160') ?>
    <h2><a href="<?= e(url('team.php', ['team' => $b])) ?>"><?= e(team_name($b)) ?></a></h2>
    <?php if ($rankB): ?><span class="cmp-meta"><?= e(t('fifa_rank')) ?> #<?= (int)$rankB ?></span><?php endif; ?>
    <?php if ($grpB): ?><span class="cmp-meta"><?= e(group_label($grpB)) ?><?= $posB ? ' · #' . (int)$posB : '' ?></span><?php endif; ?>
  </div>
</section>

<?php if ($recA['p'] > 0 || $recB['p'] > 0): ?>
<section class="fb-block cmp-block">
  <h2 class="fb-block-title">🏆 <?= e($L('سجل البطولة', 'Tournament record')) ?></h2>
  <div class="cmp-stats">
    <?php
      cmp_row($L('مباريات لُعبت', 'Matches played'), $recA['p'], $recB['p']);
      cmp_row($L('فوز', 'Wins'), $recA['w'], $recB['w']);
      cmp_row($L('تعادل', 'Draws'), $recA['d'], $recB['d']);
      cmp_row($L('خسارة', 'Losses'), $recA['l'], $recB['l'], '', true);
      cmp_row($L('أهداف له', 'Goals for'), $recA['gf'], $recB['gf']);
      cmp_row($L('أهداف عليه', 'Goals against'), $recA['ga'], $recB['ga'], '', true);
      cmp_row($L('فارق الأهداف', 'Goal difference'), $recA['gd'], $recB['gd']);
      cmp_row($L('النقاط', 'Points'), $recA['pts'], $recB['pts']);
    ?>
  </div>
</section>
<?php endif; ?>

<?php if ($fifaA || $fifaB): ?>
<section class="fb-block cmp-block">
  <h2 class="fb-block-title">📊 <?= e($L('متوسطات FIFA (لكل مباراة)', 'FIFA averages (per match)')) ?></h2>
  <div class="cmp-stats">
    <?php
      $pairs = [
          [$L('الاستحواذ', 'Possession'), 'possession', '%'],
          [$L('xG', 'xG'), 'xg', ''],
          [$L('التسديدات', 'Shots'), 'shots', ''],
          [$L('دقة التمرير', 'Pass accuracy'), 'pass_pct', '%'],
          [$L('اختراق الخطوط', 'Line breaks'), 'line_breaks', ''],
          [$L('العرضيات', 'Crosses'), 'crosses', ''],
          [$L('المسافة', 'Distance'), 'distance', $L(' كم', ' km')],
          [$L('ركض سريع', 'Sprint distance'), 'sprint_dist', $L(' كم', ' km')],
      ];
      foreach ($pairs as [$lbl, $key, $unit]) {
          cmp_row($lbl, $fifaA[$key] ?? null, $fifaB[$key] ?? null, $unit);
      }
    ?>
  </div>
  <p class="muted cmp-note"><?= e($L(
    'من تقارير FIFA الرسمية للمباريات المُحلَّلة — تظهر بعد لعب اللقاءات.',
    'From official FIFA match reports — appears once matches are analysed.'
  )) ?></p>
</section>
<?php elseif ($recA['p'] === 0 && $recB['p'] === 0): ?>
  <p class="empty-note"><?= e($L(
    'ستظهر المقارنة التفصيلية بعد انطلاق المباريات وتوفّر تقارير FIFA.',
    'Detailed comparison will appear once matches kick off and FIFA reports are available.'
  )) ?></p>
<?php endif; ?>

<?php if ($h2h): ?>
<section class="fb-block cmp-block">
  <h2 class="fb-block-title">🤝 <?= e($L('المواجهات المباشرة في البطولة', 'Head-to-head in this tournament')) ?></h2>
  <ul class="cmp-h2h">
    <?php foreach ($h2h as $hm):
      $hid = (int)($hm['_index'] ?? 0);
      $hg1 = (int)$hm['score']['ft'][0];
      $hg2 = (int)$hm['score']['ft'][1];
    ?>
      <li>
        <a href="<?= e(url('match.php', ['id' => $hid])) ?>">
          <?= flag_img($hm['team1'], 'w40') ?>
          <?= e(team_name($hm['team1'])) ?>
          <strong><?= $hg1 ?> - <?= $hg2 ?></strong>
          <?= e(team_name($hm['team2'])) ?>
          <?= flag_img($hm['team2'], 'w40') ?>
          <span class="muted">· <?= e(round_label($hm['round'] ?? '')) ?></span>
        </a>
      </li>
    <?php endforeach; ?>
  </ul>
</section>
<?php else: ?>
  <p class="muted cmp-note"><?= e($L(
    'لم تُلعب مواجهة مباشرة بينهما في البطولة بعد.',
    'No direct meeting between these teams in the tournament yet.'
  )) ?></p>
<?php endif; ?>

<div class="cmp-cta">
  <a class="btn btn-sm" href="<?= e(url('compare.php')) ?>"><?= e($L('مقارنة أخرى', 'Another comparison')) ?></a>
  <a class="btn btn-sm" href="<?= e(url('dashboard.php')) ?>">📊 <?= e($L('لوحة الإحصائيات', 'Stats dashboard')) ?></a>
</div>

<?php endif; ?>

<?php tpl('footer'); ?>
