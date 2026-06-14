<?php
/**
 * physical.php — مستكشف البيانات البدنيّة للاعبين (بأسلوب FifaPhy) من تقارير FIFA.
 * ترتيب/بحث/تبديل (إجمالي ↔ لكل مباراة) — تفاعلي بالكامل، بهويّة الموقع.
 */
require __DIR__ . '/includes/bootstrap.php';

$ar   = (current_lang() === 'ar');
$L    = fn(string $a, string $e): string => $ar ? $a : $e;
$rows = class_exists('FifaStats') ? FifaStats::physicalLeaderboard() : [];

$page_title = $L('البيانات البدنيّة', 'Physical data');
$page_desc  = $L('أرقام اللاعبين البدنيّة في كأس العالم 2026 — المسافة، العَدْوات، السرعة القصوى، رتّب وقارن.',
                 'Player physical numbers at the FIFA World Cup 2026 — distance, sprints, top speed.');
tpl('header');
?>

<div class="page-head">
  <h1>🏃 <?= e($L('البيانات البدنيّة', 'Physical data')) ?></h1>
  <p class="muted"><?= e($L('أرقام كل لاعب من تقارير FIFA الرسميّة — رتّب بأي عمود، ابحث، وبدّل بين الإجمالي والمعدّل لكل مباراة.',
                            'Every player\'s numbers from official FIFA reports — sort by any column, search, toggle total vs per-match.')) ?></p>
</div>

<?php if (!$rows): ?>
  <p class="empty-note"><?= e($L('تظهر البيانات بعد لعب المباريات.', 'Data appears once matches are played.')) ?></p>
<?php else: ?>

<div class="phys-controls">
  <input type="search" id="physSearch" class="phys-input" placeholder="<?= e($L('ابحث عن لاعب أو منتخب…', 'Search player or team…')) ?>">
  <div class="phys-toggle">
    <button type="button" class="phys-mode is-on" data-mode="total"><?= e($L('الإجمالي', 'Total')) ?></button>
    <button type="button" class="phys-mode" data-mode="avg"><?= e($L('لكل مباراة', 'Per match')) ?></button>
  </div>
</div>

<div class="lb-wrap">
  <table class="leaderboard phys-table" id="physTable">
    <thead>
      <tr>
        <th>#</th>
        <th class="lb-name"><?= e($L('اللاعب', 'Player')) ?></th>
        <th class="ph-sort" data-k="m"><?= e($L('مباريات', 'M')) ?></th>
        <th class="ph-sort is-sort" data-k="dist"><?= e($L('المسافة (كم)', 'Distance (km)')) ?></th>
        <th class="ph-sort" data-k="sprints"><?= e($L('عَدْوات', 'Sprints')) ?></th>
        <th class="ph-sort" data-k="hsr"><?= e($L('ركضات عالية', 'HS runs')) ?></th>
        <th class="ph-sort" data-k="top"><?= e($L('سرعة قصوى', 'Top speed')) ?></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($rows as $i => $r):
        $teamAr = function_exists('team_name') ? team_name($r['team']) : $r['team'];
        $topTxt = rtrim(rtrim(number_format((float)$r['top'], 1, '.', ''), '0'), '.');
      ?>
      <tr data-name="<?= e(mb_strtolower($r['name'], 'UTF-8')) ?>"
          data-team="<?= e(mb_strtolower($teamAr . ' ' . $r['team'], 'UTF-8')) ?>"
          data-m="<?= (int)$r['m'] ?>" data-dist="<?= (float)$r['dist'] ?>"
          data-sprints="<?= (int)$r['sprints'] ?>" data-hsr="<?= (int)$r['hsr'] ?>" data-top="<?= (float)$r['top'] ?>">
        <td class="lb-rank ph-rank"><?= $i + 1 ?></td>
        <td class="lb-name"><?= flag_img($r['team'], 'w40') ?> <?= e($r['name']) ?>
            <span class="muted ph-team"><?= e($teamAr) ?></span></td>
        <td><?= (int)$r['m'] ?></td>
        <td class="ph-v" data-c="dist"><?= round((float)$r['dist'] / 1000, 1) ?></td>
        <td class="ph-v" data-c="sprints"><?= (int)$r['sprints'] ?></td>
        <td class="ph-v" data-c="hsr"><?= (int)$r['hsr'] ?></td>
        <td class="ph-v" data-c="top"><?= e($topTxt) ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<p class="video-credit"><?= e($L('المصدر: المركز الفنّي لـFIFA — تقارير ما بعد المباراة. «لكل مباراة» = الإجمالي ÷ عدد المباريات.',
                                  'Source: FIFA Training Centre — Post-Match reports. “Per match” = total ÷ matches played.')) ?></p>

<style>
.phys-controls{display:flex;gap:10px;flex-wrap:wrap;align-items:center;margin:10px 0 14px}
.phys-input{flex:1;min-width:200px;padding:10px 14px;border-radius:10px;border:1px solid rgba(255,255,255,.15);background:rgba(255,255,255,.05);color:inherit;font:inherit}
.phys-toggle{display:flex;border:1px solid rgba(255,255,255,.15);border-radius:10px;overflow:hidden}
.phys-mode{padding:9px 18px;background:transparent;color:inherit;border:0;cursor:pointer;font-weight:700;font:inherit}
.phys-mode.is-on{background:#ffc846;color:#0a1626}
.phys-table th.ph-sort{cursor:pointer;white-space:nowrap}
.phys-table th.ph-sort.is-sort{color:#ffc846}
.phys-table .ph-v{font-variant-numeric:tabular-nums;font-weight:700}
.phys-table .ph-rank{font-weight:800;color:#ffc846}
.phys-table .ph-team{display:block;font-size:.78em;opacity:.7}
</style>
<script>
(function(){
  var table = document.getElementById('physTable'); if (!table) return;
  var tbody = table.tBodies[0];
  var rows  = [].slice.call(tbody.rows);
  var mode  = 'total', sortK = 'dist';
  var val = function(r, k){ return parseFloat(r.getAttribute('data-' + k)) || 0; };
  var metric = function(r, k){
    var v = val(r, k);
    if (mode === 'avg' && k !== 'top' && k !== 'm') v = v / (val(r, 'm') || 1);
    return v;
  };
  function render(){
    rows.forEach(function(r){
      var m = val(r, 'm') || 1, f = (mode === 'avg') ? m : 1;
      r.querySelector('[data-c=dist]').textContent    = (val(r, 'dist') / 1000 / f).toFixed(1);
      r.querySelector('[data-c=sprints]').textContent = (mode === 'avg') ? (val(r, 'sprints') / m).toFixed(1) : val(r, 'sprints');
      r.querySelector('[data-c=hsr]').textContent     = (mode === 'avg') ? (val(r, 'hsr') / m).toFixed(1) : val(r, 'hsr');
    });
  }
  function sortBy(k){
    sortK = k;
    rows.sort(function(a, b){ return metric(b, k) - metric(a, k); });
    rows.forEach(function(r, i){ tbody.appendChild(r); r.querySelector('.ph-rank').textContent = i + 1; });
  }
  [].forEach.call(table.querySelectorAll('.ph-sort'), function(th){
    th.addEventListener('click', function(){
      [].forEach.call(table.querySelectorAll('.ph-sort'), function(x){ x.classList.remove('is-sort'); });
      th.classList.add('is-sort'); sortBy(th.getAttribute('data-k'));
    });
  });
  [].forEach.call(document.querySelectorAll('.phys-mode'), function(btn){
    btn.addEventListener('click', function(){
      [].forEach.call(document.querySelectorAll('.phys-mode'), function(x){ x.classList.remove('is-on'); });
      btn.classList.add('is-on'); mode = btn.getAttribute('data-mode'); render(); sortBy(sortK);
    });
  });
  var s = document.getElementById('physSearch');
  if (s) s.addEventListener('input', function(){
    var q = this.value.trim().toLowerCase();
    rows.forEach(function(r){
      var hit = !q || r.getAttribute('data-name').indexOf(q) >= 0 || r.getAttribute('data-team').indexOf(q) >= 0;
      r.style.display = hit ? '' : 'none';
    });
  });
})();
</script>
<?php endif; ?>

<?php tpl('footer'); ?>
