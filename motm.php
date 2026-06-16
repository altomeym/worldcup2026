<?php
/**
 * motm.php — صفحة «رجل المباراة»: تجمع كل من نالوا اللقب عبر مباريات البطولة.
 * المصدر: نموذج التقييم (أعلى تقييم لكل مباراة، ≥45 دقيقة) — FifaMetrics::motmFor().
 * تمرّ على المباريات المنتهية وتعرض رجل كل مباراة + نتيجتها + رابط ملفّه + مشاركة.
 */
require __DIR__ . '/includes/bootstrap.php';

$ar = (current_lang() === 'ar');
$L  = fn(string $a, string $e): string => $ar ? $a : $e;

// اجمع رجل المباراة لكل مباراة منتهية، الأحدث أوّلاً
$list = [];
if (class_exists('FifaMetrics') && class_exists('DataService')) {
    foreach (DataService::allMatches() as $m) {
        $ft = $m['score']['ft'] ?? null;
        if (!(is_array($ft) && isset($ft[0], $ft[1]) && is_numeric($ft[0]) && is_numeric($ft[1]))) continue;
        $t1 = (string)($m['team1'] ?? ''); $t2 = (string)($m['team2'] ?? '');
        $rec = FifaMetrics::motmFor($t1, $t2);
        if (!$rec) continue;
        $list[] = [
            'm'   => $m,
            'rec' => $rec,
            'team'=> FifaMetrics::motmTeamEn($rec, $t1, $t2),
            'ts'  => DataService::matchTimestamp($m) ?? 0,
        ];
    }
    usort($list, fn($a, $b) => ($b['ts'] ?? 0) <=> ($a['ts'] ?? 0));
}

$page_title = $L('رجل المباراة', 'Player of the Match');
$page_desc  = $L('كل من نالوا لقب رجل المباراة في كأس العالم 2026 — حسب أعلى تقييم في كل مباراة من بيانات FIFA.',
                 'Every Player of the Match at the FIFA World Cup 2026 — the top-rated player in each match from FIFA data.');
$page_image = url('card_img.php', ['mode' => 'dashboard', 'd' => card_rev()]);
tpl('header');
?>

<div class="page-head">
  <h1>🌟 <?= e($L('رجل المباراة', 'Player of the Match')) ?></h1>
  <p class="muted"><?= e($L('أفضل لاعب في كل مباراة — حسب أعلى تقييم (من بيانات FIFA الرسميّة).',
                            'The standout player of each match — by top rating (from official FIFA data).')) ?></p>
</div>

<?php if (!$list): ?>
  <p class="empty-note"><?= e($L('يظهر رجال المباريات بعد لعب المباريات.', 'Players of the Match appear once matches are played.')) ?></p>
<?php else: ?>
<div class="motm-grid">
  <?php foreach ($list as $row):
      $m = $row['m']; $rec = $row['rec']; $teamEn = $row['team'];
      $t1 = (string)($m['team1'] ?? ''); $t2 = (string)($m['team2'] ?? '');
      $ft = $m['score']['ft']; $photo = (string)($rec['photo'] ?? '');
      $purl = url('player.php', ['id' => (string)($rec['pid'] ?? '')]);   // pid دقيق (الاسم قد يختلف)
      $murl = url('match.php', ['id' => (int)($m['_index'] ?? 0)]);
  ?>
  <div class="motm-item">
    <a class="motm-item-player" href="<?= e($purl) ?>">
      <?php if ($photo !== ''): ?>
        <img class="motm-item-photo" src="<?= e($photo) ?>" alt="" loading="lazy" onerror="this.classList.add('off')">
      <?php else: ?><span class="motm-item-photo motm-item-noimg">★</span><?php endif; ?>
      <span class="motm-item-info">
        <b class="motm-item-name"><?= e($rec['name'] ?? '') ?></b>
        <span class="motm-item-team"><?= flag_img($teamEn, 'w40') ?> <?= e(team_name($teamEn)) ?></span>
      </span>
      <span class="motm-item-rating">★ <?= number_format((float)($rec['rating'] ?? 0), 1) ?></span>
    </a>
    <a class="motm-item-match" href="<?= e($murl) ?>">
      <?= flag_img($t1, 'w40') ?> <span class="motm-sc"><?= (int)$ft[0] ?>–<?= (int)$ft[1] ?></span> <?= flag_img($t2, 'w40') ?>
      <span class="motm-vs"><?= e(team_name($t1)) ?> — <?= e(team_name($t2)) ?></span>
    </a>
  </div>
  <?php endforeach; ?>
</div>
<p class="video-credit"><?= e($L('رجل المباراة = أعلى لاعب تقييماً (لعب 45 دقيقة فأكثر) حسب نموذج تقييم تقريبيّ على بيانات FIFA.',
                                  'Player of the Match = the highest-rated player (45+ min) per an approximate rating model on FIFA data.')) ?></p>
<?php endif; ?>

<style>
.motm-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:14px;margin:10px 0 18px}
.motm-item{background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.09);border-radius:16px;overflow:hidden}
.motm-item-player{display:flex;align-items:center;gap:13px;padding:14px 16px;text-decoration:none;color:inherit;
  background:linear-gradient(135deg,rgba(255,200,70,.14),rgba(38,206,168,.05));transition:.15s}
.motm-item-player:hover{background:linear-gradient(135deg,rgba(255,200,70,.22),rgba(38,206,168,.09))}
.motm-item-photo{width:64px;height:64px;border-radius:50%;object-fit:cover;object-position:top center;
  background:#0e1b34;border:2px solid #ffc846;flex:0 0 auto;display:flex;align-items:center;justify-content:center}
.motm-item-photo.off{display:none}
.motm-item-noimg{font-size:1.6rem;color:#ffc846}
.motm-item-info{display:flex;flex-direction:column;gap:4px;min-width:0}
.motm-item-name{font-size:1.08rem;line-height:1.1}
.motm-item-team{display:flex;align-items:center;gap:6px;opacity:.9;font-size:.86rem}
.motm-item-team .flag{width:22px;height:auto;border-radius:2px}
.motm-item-rating{margin-inline-start:auto;background:#ffc846;color:#0a1626;font-weight:800;padding:5px 12px;border-radius:12px;white-space:nowrap}
.motm-item-match{display:flex;align-items:center;gap:8px;padding:10px 16px;text-decoration:none;color:inherit;
  border-top:1px solid rgba(255,255,255,.08);font-size:.85rem}
.motm-item-match:hover{background:rgba(255,255,255,.04)}
.motm-item-match .flag{width:26px;height:auto;border-radius:3px}
.motm-sc{font-weight:800;font-variant-numeric:tabular-nums}
.motm-vs{opacity:.7;margin-inline-start:4px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
</style>

<?php render_share(
  url('motm.php', ['d' => card_rev()]),
  $L('رجال مباريات كأس العالم 2026 — أفضل لاعب في كل مباراة', 'FIFA World Cup 2026 — Players of the Match')
); ?>

<?php tpl('footer'); ?>
