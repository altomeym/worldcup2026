<?php
/**
 * dashboard.php — لوحة الإحصائيّات من تقارير FIFA الرسميّة (PMSR).
 * مؤشّرات البطولة (KPI) + جدول مقارنة المنتخبات (استحواذ/xG/تسديدات/تمرير/مسافة) مع
 * أشرطة وفرز. كل البيانات من FifaStats::teamDashboard().
 */
require __DIR__ . '/includes/bootstrap.php';

$ar = (current_lang() === 'ar');
$L  = fn(string $a, string $e): string => $ar ? $a : $e;
$D  = class_exists('FifaStats') ? FifaStats::teamDashboard() : ['teams' => [], 'kpi' => []];
$teams = $D['teams'] ?? [];
$kpi   = $D['kpi'] ?? [];

$page_title = $L('لوحة الإحصائيّات', 'Stats Dashboard');
$page_desc  = $L('لوحة إحصائيّة شاملة من تقارير FIFA الرسميّة — مقارنات المنتخبات، المتوسّطات، ومؤشّرات البطولة.',
                 'Full stats dashboard from official FIFA reports — team comparisons, averages and tournament leaders.');
// معاينة المشاركة: بطاقة مؤشّرات اللوحة بهويّة الموقع
$page_image = url('card_img.php', ['mode' => 'dashboard', 'd' => card_rev()]);
tpl('header');

/** بطاقة مؤشّر */
function kpi_card(string $icon, string $big, string $label, string $sub = ''): void { ?>
  <div class="db-kpi">
    <div class="db-kpi-ico"><?= $icon ?></div>
    <div class="db-kpi-big"><?= e($big) ?></div>
    <div class="db-kpi-lbl"><?= e($label) ?></div>
    <?php if ($sub !== ''): ?><div class="db-kpi-sub"><?= e($sub) ?></div><?php endif; ?>
  </div>
<?php }
?>

<div class="page-head">
  <h1>📊 <?= e($L('لوحة الإحصائيّات', 'Stats Dashboard')) ?></h1>
  <p class="muted"><?= e($L('من تقارير FIFA الرسميّة — مقارنات المنتخبات والمتوسّطات ومؤشّرات البطولة.',
                            'From official FIFA reports — team comparisons, averages and tournament leaders.')) ?></p>
</div>

<?php if (!$teams): ?>
  <p class="empty-note"><?= e($L('تظهر اللوحة بعد لعب المباريات.', 'The dashboard appears once matches are played.')) ?></p>
<?php else:
  $fastest = $kpi['fastest'] ?? []; $topDist = $kpi['topDist'] ?? [];
  $topSprint = $kpi['topSprint'] ?? []; $topXg = $kpi['topXg'] ?? [];
  $tn = fn($en) => function_exists('team_name') ? team_name((string)$en) : (string)$en;
?>

<div class="db-kpis">
  <?php
    kpi_card('🎮', (string)(int)($kpi['matches'] ?? 0), $L('مباريات محلَّلة', 'Matches analysed'));
    kpi_card('🏃', number_format((float)($kpi['distance'] ?? 0), 0) . ' ' . $L('كم', 'km'), $L('إجمالي المسافة', 'Total distance'));
    kpi_card('⚡', rtrim(rtrim(number_format((float)($fastest['v'] ?? 0), 1, '.', ''), '0'), '.') . ' ' . $L('كم/س', 'km/h'),
             $L('أسرع لاعب', 'Fastest player'), (string)($fastest['name'] ?? '') . ' · ' . $tn($fastest['team'] ?? ''));
    kpi_card('📏', number_format((float)($topDist['v'] ?? 0) / 1000, 1) . ' ' . $L('كم', 'km'),
             $L('أكثر مسافة/مباراة', 'Most distance/match'), (string)($topDist['name'] ?? '') . ' · ' . $tn($topDist['team'] ?? ''));
    kpi_card('💨', number_format((float)($topSprint['v'] ?? 0), 0),
             $L('أكثر عَدْوات/مباراة', 'Most sprints/match'), (string)($topSprint['name'] ?? '') . ' · ' . $tn($topSprint['team'] ?? ''));
    kpi_card('🎯', number_format((float)($topXg['v'] ?? 0), 2),
             $L('أعلى xG لمباراة', 'Highest xG (match)'), $tn($topXg['team'] ?? ''));
  ?>
</div>

<?php
  // ─── رسمان بيانيّان: أحسن 10 منتخبات (مسافة/مباراة · دقّة التمرير) ───
  $topChart = function (string $key, string $title, string $unit, int $dec, string $color) use ($teams, $tn) {
      $rows = $teams;
      usort($rows, fn($a, $b) => (float)($b[$key] ?? 0) <=> (float)($a[$key] ?? 0));
      $rows = array_slice($rows, 0, 10);
      $max  = 0.0; foreach ($rows as $r) $max = max($max, (float)($r[$key] ?? 0));
      if ($max <= 0) return;
      echo '<div class="db-chart"><h3 class="db-chart-h">' . e($title) . '</h3>';
      foreach ($rows as $i => $r) {
          $v = (float)($r[$key] ?? 0); $pct = round($v / $max * 100);
          echo '<div class="dbc-row"><span class="dbc-rank">' . ($i + 1) . '</span>'
             . '<span class="dbc-team">' . flag_img($r['team'], 'w40') . ' ' . e($tn($r['team'])) . '</span>'
             . '<span class="dbc-track"><i style="width:' . (int)$pct . '%;background:' . $color . '"></i></span>'
             . '<b class="dbc-val">' . number_format($v, $dec) . '<small>' . e($unit) . '</small></b></div>';
      }
      echo '</div>';
  };
?>
<div class="db-charts">
  <?php
    $topChart('distance', $L('🏃 أحسن 10 — المسافة لكل مباراة', '🏃 Top 10 — distance/match'), $L('كم', 'km'), 1, '#26cea8');
    $topChart('pass_pct', $L('🎯 أحسن 10 — دقّة التمرير', '🎯 Top 10 — pass accuracy'), '%', 0, '#ffc846');
  ?>
</div>

<?php
  // أعمدة الجدول (كلّها لكل مباراة — متوسّط): المفتاح، العنوان، عدد المنازل العشريّة
  $cols = [
    'distance'    => [$L('مسافة/مباراة (كم)', 'Distance/match (km)'), 1],
    'possession'  => [$L('استحواذ %', 'Possession %'), 1],
    'xg'          => [$L('xG/مباراة', 'xG/match'), 2],
    'shots'       => [$L('تسديدات/مباراة', 'Shots/match'), 1],
    'pass_pct'    => [$L('دقّة التمرير %', 'Pass acc %'), 0],
    'line_breaks' => [$L('اختراق خطوط/مباراة', 'Line breaks/match'), 1],
  ];
  $maxC = [];
  foreach ($cols as $k => $_) { $maxC[$k] = 0.0; foreach ($teams as $t) $maxC[$k] = max($maxC[$k], (float)($t[$k] ?? 0)); }
?>
<div class="lb-wrap">
  <table class="leaderboard db-table" id="dbTable">
    <thead>
      <tr>
        <th class="lb-name"><?= e($L('المنتخب', 'Team')) ?></th>
        <th class="db-sort" data-k="m"><?= e($L('م', 'M')) ?></th>
        <?php foreach ($cols as $k => $c): ?>
          <th class="db-sort<?= $k === 'distance' ? ' is-sort' : '' ?>" data-k="<?= e($k) ?>"><?= e($c[0]) ?></th>
        <?php endforeach; ?>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($teams as $t): ?>
      <tr data-m="<?= (int)$t['m'] ?>" <?php foreach ($cols as $k => $_): ?>data-<?= e($k) ?>="<?= (float)($t[$k] ?? 0) ?>" <?php endforeach; ?>>
        <td class="lb-name"><?= flag_img($t['team'], 'w40') ?> <?= e($tn($t['team'])) ?></td>
        <td><?= (int)$t['m'] ?></td>
        <?php foreach ($cols as $k => $c):
          $v = (float)($t[$k] ?? 0); $pct = $maxC[$k] > 0 ? round($v / $maxC[$k] * 100) : 0;
          $txt = number_format($v, (int)$c[1], '.', $c[1] ? '.' : ',');
        ?>
          <td class="db-cell"><span class="db-bar" style="width:<?= (int)$pct ?>%"></span><b><?= e($txt) ?></b></td>
        <?php endforeach; ?>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<p class="video-credit"><?= e($L('المصدر: المركز الفنّي لـFIFA — تقارير ما بعد المباراة. كلّ القيم متوسّط لكل مباراة.',
                                  'Source: FIFA Training Centre — Post-Match reports. All values are per-match averages.')) ?></p>

<style>
.db-kpis{display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:12px;margin:8px 0 22px}
.db-kpi{background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.10);border-radius:14px;padding:16px;text-align:center}
.db-kpi-ico{font-size:1.5rem}
.db-kpi-big{font-size:1.7rem;font-weight:800;color:#ffc846;font-variant-numeric:tabular-nums;margin-top:4px}
.db-kpi-lbl{font-size:.9rem;opacity:.95;margin-top:2px}
.db-kpi-sub{font-size:.74rem;opacity:.7;margin-top:4px}
.db-table th.db-sort{cursor:pointer;white-space:nowrap}
.db-table th.db-sort.is-sort{color:#ffc846}
.db-table .db-cell{position:relative;font-variant-numeric:tabular-nums;font-weight:700;min-width:120px}
.db-table .db-cell .db-bar{position:absolute;inset-inline-start:0;top:50%;transform:translateY(-50%);height:60%;
  background:linear-gradient(90deg,rgba(255,200,70,.30),rgba(255,200,70,.10));border-radius:6px;z-index:0}
.db-table .db-cell b{position:relative;z-index:1}
.db-charts{display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:16px;margin:4px 0 22px}
.db-chart{background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:14px;padding:14px 16px}
.db-chart-h{font-size:1rem;margin:0 0 12px;color:var(--accent,#fff)}
.dbc-row{display:grid;grid-template-columns:20px minmax(96px,150px) 1fr auto;align-items:center;gap:9px;margin:7px 0}
.dbc-rank{font-weight:800;color:#ffc846;font-size:.8rem;text-align:center}
.dbc-team{display:flex;align-items:center;gap:6px;font-size:.84rem;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.dbc-team .flag{width:22px;height:auto;border-radius:2px;flex:0 0 auto}
.dbc-track{height:9px;border-radius:6px;background:rgba(255,255,255,.08);overflow:hidden}
.dbc-track i{display:block;height:100%;border-radius:6px}
.dbc-val{font-variant-numeric:tabular-nums;font-size:.9rem;white-space:nowrap}
.dbc-val small{opacity:.6;font-size:.72em;margin-inline-start:2px;font-weight:600}
</style>
<script>
(function(){
  var table = document.getElementById('dbTable'); if (!table) return;
  var tbody = table.tBodies[0], rows = [].slice.call(tbody.rows);
  function val(r, k){ return parseFloat(r.getAttribute('data-' + k)) || 0; }
  function sortBy(k){
    rows.sort(function(a, b){ return val(b, k) - val(a, k); });
    rows.forEach(function(r){ tbody.appendChild(r); });
  }
  [].forEach.call(table.querySelectorAll('.db-sort'), function(th){
    th.addEventListener('click', function(){
      [].forEach.call(table.querySelectorAll('.db-sort'), function(x){ x.classList.remove('is-sort'); });
      th.classList.add('is-sort'); sortBy(th.getAttribute('data-k'));
    });
  });
})();
</script>
<?php endif; ?>

<?php render_share(
  url('dashboard.php', ['d' => card_rev()]),
  $L('لوحة إحصائيّات كأس العالم 2026 — من تقارير FIFA الرسميّة', 'FIFA World Cup 2026 stats dashboard — from official FIFA reports')
); ?>

<?php tpl('footer'); ?>
