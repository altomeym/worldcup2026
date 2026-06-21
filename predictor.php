<?php
/**
 * predictor.php — توقّعات لاعب واحد مقابل النتائج الفعليّة (تُفتح بالضغط على اسمه في الصدارة).
 * ?u=NICKNAME
 */
require __DIR__ . '/includes/bootstrap.php';

$nick = trim((string)($_GET['u'] ?? ''));
$data = ($nick !== '' && class_exists('Predictions')) ? Predictions::userPredictions($nick) : null;

$L  = fn(string $ar, string $en, ?string $fr = null) => current_lang() === 'ar' ? $ar : (current_lang() === 'fr' ? ($fr ?? $en) : $en);
$page_title = $data ? ($L('توقّعات ', 'Predictions of ') . $data['nickname']) : $L('توقّعات اللاعب', 'Player predictions');
$page_desc  = $data
    ? $L($data['nickname'] . ' في مسابقة توقّعات كأس العالم 2026 — ' . (int)$data['points'] . ' نقطة.',
         $data['nickname'] . " in the World Cup 2026 predictions game — " . (int)$data['points'] . ' pts.')
    : '';

tpl('header');
?>

<div class="page-head">
  <h1>🎯 <?= e($L('توقّعات', 'Predictions') . ($data ? ' · ' . $data['nickname'] : '')) ?></h1>
  <p class="muted">
    <a class="section-link" href="<?= e(url('leaderboard.php')) ?>"><?= e($L('الصدارة', 'Leaderboard')) ?> ›</a>
  </p>
</div>

<?php if (!$data): ?>
  <p class="empty-note"><?= e($L('لم يُعثَر على هذا اللاعب.', 'Player not found.')) ?></p>
<?php else: ?>

  <div class="pr-stats">
    <div class="pr-stat"><span class="pr-v"><?= (int)$data['points'] ?></span><span class="pr-l"><?= e($L('نقاط التوقّعات', 'Prediction pts')) ?></span></div>
    <div class="pr-stat"><span class="pr-v"><?= (int)$data['exact'] ?></span><span class="pr-l"><?= e($L('مضبوط', 'exact')) ?></span></div>
    <div class="pr-stat"><span class="pr-v"><?= (int)$data['correct'] ?></span><span class="pr-l"><?= e($L('صحيح', 'correct')) ?></span></div>
    <div class="pr-stat"><span class="pr-v"><?= (int)$data['played'] ?></span><span class="pr-l"><?= e($L('مباراة', 'played')) ?></span></div>
  </div>

  <table class="pr-table">
    <thead>
      <tr>
        <th><?= e($L('المباراة', 'Match')) ?></th>
        <th><?= e($L('توقّعه', 'Pick')) ?></th>
        <th><?= e($L('الفعلي', 'Actual')) ?></th>
        <th><?= e($L('النقاط', 'Pts')) ?></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($data['rows'] as $r):
        $m = $r['m'];
        $t1 = function_exists('team_name') ? team_name((string)($m['team1'] ?? '')) : (string)($m['team1'] ?? '');
        $t2 = function_exists('team_name') ? team_name((string)($m['team2'] ?? '')) : (string)($m['team2'] ?? '');
        $pts = $r['pts'];
        $cls = $pts === null ? 'p-pend' : ($pts === 3 ? 'p-3' : ($pts === 2 ? 'p-2' : ($pts === 1 ? 'p-1' : 'p-0')));
      ?>
      <tr>
        <td class="pr-match"><?= e($t1) ?> <span class="pr-vs"><?= e($L('ضد', 'v')) ?></span> <?= e($t2) ?></td>
        <td class="pr-score"><?= (int)$r['p1'] ?> - <?= (int)$r['p2'] ?></td>
        <td class="pr-score"><?= $r['a1'] === null ? '—' : ((int)$r['a1'] . ' - ' . (int)$r['a2']) ?></td>
        <td><span class="pr-pts <?= $cls ?>"><?= $pts === null ? '—' : (int)$pts ?></span></td>
      </tr>
      <?php endforeach; ?>
      <?php if (!$data['rows']): ?>
      <tr><td colspan="4" class="empty-note"><?= e($L('لا توقّعات بعد.', 'No predictions yet.')) ?></td></tr>
      <?php endif; ?>
    </tbody>
  </table>

  <style>
  .pr-stats{display:flex;gap:10px;flex-wrap:wrap;margin:14px 0 18px}
  .pr-stat{flex:1;min-width:80px;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.12);
    border-radius:12px;padding:12px 8px;text-align:center}
  .pr-stat .pr-v{display:block;font-size:1.6rem;font-weight:800;color:var(--accent,#fff)}
  .pr-stat .pr-l{display:block;font-size:.78rem;opacity:.7;margin-top:2px}
  .pr-table{width:100%;border-collapse:collapse;background:rgba(255,255,255,.03);
    border:1px solid rgba(255,255,255,.1);border-radius:12px;overflow:hidden}
  .pr-table th,.pr-table td{padding:11px 12px;border-bottom:1px solid rgba(255,255,255,.08);text-align:center;font-size:.92rem}
  .pr-table th{background:rgba(255,255,255,.04);font-size:.78rem;opacity:.85;text-transform:uppercase;letter-spacing:.03em}
  .pr-table tr:last-child td{border-bottom:0}
  .pr-match{text-align:start;font-weight:600}
  .pr-vs{opacity:.5;font-weight:400;margin:0 4px}
  .pr-score{font-variant-numeric:tabular-nums;white-space:nowrap}
  .pr-pts{display:inline-block;min-width:30px;padding:3px 9px;border-radius:20px;font-weight:800;font-variant-numeric:tabular-nums}
  .pr-pts.p-3{background:#ffc846;color:#0a1626}
  .pr-pts.p-2{background:#2dd4bf;color:#04221d}
  .pr-pts.p-1{background:rgba(255,255,255,.22);color:#fff}
  .pr-pts.p-0{background:rgba(255,255,255,.07);color:#fca5a5}
  .pr-pts.p-pend{background:rgba(255,255,255,.05);color:#9aa6c4}
  </style>

  <?php render_share(canonical_url(), $page_title . ' — ' . SITE_NAME_AR); ?>

<?php endif; ?>

<?php tpl('footer'); ?>
