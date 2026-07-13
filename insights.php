<?php
/**
 * insights.php — قائمة المقالات التحليلية الأصلية (foot-boll).
 */
require __DIR__ . '/includes/bootstrap.php';

$ar = (current_lang() === 'ar');
$L  = fn(string $a, string $e) => $ar ? $a : $e;
$list = Insights::all();

$page_title = $L('تحليلات foot-boll', 'foot-boll Insights');
$page_desc  = $L(
    'مقالات تحليلية أسبوعية أصلية عن كأس العالم 2026 — قوانين البطولة، قراءة إحصائيات FIFA، ونصائح التوقعات على foot-boll.',
    'Original weekly analytical articles on World Cup 2026 — tournament format, reading FIFA stats, and prediction tips on foot-boll.'
);
$page_keywords = $L(
    'مقالات تحليلية, foot-boll, كأس العالم 2026, تحليل',
    'analytical articles, foot-boll, World Cup 2026, analysis'
);

tpl('header');
?>

<div class="page-head">
  <h1>📝 <?= e($page_title) ?></h1>
  <p class="muted"><?= e($L(
    'محتوى تحريري مكتوب بأيدي فريق foot-boll — ليس أخباراً مُعاد نشرها. نشر أسبوعي تقريباً.',
    'Editorial content written by the foot-boll team — not republished news. Roughly weekly.'
  )) ?></p>
</div>

<?php if (!$list): ?>
  <p class="empty-note"><?= e($L('المقالات التحليلية قادمة قريباً.', 'Analytical articles coming soon.')) ?></p>
<?php else: ?>
  <div class="insights-grid">
    <?php foreach ($list as $it):
      $title   = Insights::field($it, 'title');
      $excerpt = Insights::field($it, 'excerpt');
      $pub     = (string)($it['published'] ?? '');
      $tags    = $it['tags_' . ($ar ? 'ar' : 'en')] ?? $it['tags_en'] ?? [];
    ?>
      <article class="insight-card">
        <span class="insight-date"><?= local_dt(strtotime($pub . ' 12:00:00'), 'date') ?></span>
        <h2><a href="<?= e(Insights::url($it)) ?>"><?= e($title) ?></a></h2>
        <p class="insight-excerpt"><?= e($excerpt) ?></p>
        <?php if (is_array($tags) && $tags): ?>
          <div class="insight-tags">
            <?php foreach ($tags as $tag): ?>
              <span class="insight-tag"><?= e((string)$tag) ?></span>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
        <a class="fb-block-link" href="<?= e(Insights::url($it)) ?>"><?= e($L('اقرأ المقال ←', 'Read article →')) ?></a>
      </article>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php tpl('footer'); ?>
