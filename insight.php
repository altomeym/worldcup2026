<?php
/**
 * insight.php — مقال تحليلي واحد (?slug=).
 */
require __DIR__ . '/includes/bootstrap.php';

$ar   = (current_lang() === 'ar');
$L    = fn(string $a, string $e) => $ar ? $a : $e;
$slug = isset($_GET['slug']) ? trim((string)$_GET['slug']) : '';
$item = Insights::find($slug);

if ($item === null) {
    http_response_code(404);
    $page_title  = $L('تحليلات foot-boll', 'foot-boll Insights');
    $page_robots = 'noindex,follow';
    tpl('header');
    echo '<div class="alert">' . e($L('المقال غير موجود.', 'Article not found.')) . '</div>';
    echo '<p><a class="btn" href="' . e(url('insights.php')) . '">‹ ' . e($L('كل المقالات', 'All articles')) . '</a></p>';
    tpl('footer');
    exit;
}

$title   = Insights::field($item, 'title');
$excerpt = Insights::field($item, 'excerpt');
$body    = Insights::body($item);
$pub     = (string)($item['published'] ?? '');
$tags    = $item['tags_' . ($ar ? 'ar' : 'en')] ?? $item['tags_en'] ?? [];

$page_title = $title;
$page_desc  = mb_substr($excerpt, 0, 160);
$page_keywords = is_array($tags) ? implode(', ', $tags) . ', foot-boll' : 'foot-boll';
$seo_type   = 'article';
$page_robots = 'index,follow';

gtm_add([
    'insight_slug'  => $slug,
    'insight_title' => $title,
    'content_group' => 'editorial_insight',
]);
tpl('header');
?>

<a class="back-link" href="<?= e(url('insights.php')) ?>">‹ <?= e($L('تحليلات foot-boll', 'foot-boll Insights')) ?></a>

<article class="article-view insight-view">
  <header class="insight-header">
    <?php if ($pub !== ''): ?>
      <span class="insight-date"><?= local_dt(strtotime($pub . ' 12:00:00'), 'date') ?></span>
    <?php endif; ?>
    <span class="article-editorial-label"><?= e($L('محتوى أصلي — foot-boll', 'Original content — foot-boll')) ?></span>
  </header>

  <h1 class="article-title"><?= e($title) ?></h1>

  <?php if ($excerpt !== ''): ?>
    <p class="insight-lead"><?= e($excerpt) ?></p>
  <?php endif; ?>

  <div class="insight-body prose">
    <?php foreach ($body as $para): ?>
      <p><?= e($para) ?></p>
    <?php endforeach; ?>
  </div>

  <?php if (is_array($tags) && $tags): ?>
    <div class="insight-tags insight-tags-foot">
      <?php foreach ($tags as $tag): ?>
        <span class="insight-tag"><?= e((string)$tag) ?></span>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <div class="insight-cta-row">
    <a class="btn btn-sm" href="<?= e(url('compare.php')) ?>">⚖️ <?= e($L('قارن منتخبين', 'Compare teams')) ?></a>
    <a class="btn btn-sm" href="<?= e(url('predict.php')) ?>">🎯 <?= e(t('competition')) ?></a>
    <a class="btn btn-sm" href="<?= e(url('dashboard.php')) ?>">📊 <?= e($L('لوحة الإحصائيات', 'Stats dashboard')) ?></a>
  </div>

  <?php render_share(canonical_url(), $title); ?>
</article>

<?php
$more = array_values(array_filter(Insights::all(), fn($x) => ($x['slug'] ?? '') !== $slug));
$more = array_slice($more, 0, 3);
if ($more):
?>
<section class="fb-block" style="margin-top:30px">
  <div class="fb-block-head">
    <h2><span class="fb-block-bar"></span><?= e($L('مقالات أخرى', 'More articles')) ?></h2>
    <a class="fb-block-link" href="<?= e(url('insights.php')) ?>"><?= e($L('كل المقالات', 'All articles')) ?> ›</a>
  </div>
  <div class="insights-grid insights-grid--sm">
    <?php foreach ($more as $it): ?>
      <article class="insight-card">
        <h3><a href="<?= e(Insights::url($it)) ?>"><?= e(Insights::field($it, 'title')) ?></a></h3>
        <p class="insight-excerpt"><?= e(Insights::field($it, 'excerpt')) ?></p>
      </article>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<?php tpl('footer'); ?>
