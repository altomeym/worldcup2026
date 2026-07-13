<?php
/**
 * news.php — أخبار كأس العالم (RSS مجاني، يتحدّث تلقائياً).
 */
require __DIR__ . '/includes/bootstrap.php';

$ar = (current_lang() === 'ar');
$items = News::latest();

$page_title = t('latest_news');
$page_desc  = $ar
    ? 'أخبار كأس العالم 2026 على foot-boll — عناوين مختارة مع سياق تحريري يربط كل خبر بمنتخبات ومباريات البطولة. لا نعيد نشر المقالات.'
    : 'World Cup 2026 news on foot-boll — curated headlines with editorial context linking each story to tournament teams and matches.';
$page_keywords = $ar
    ? 'أخبار مونديال 2026, foot-boll, كأس العالم, أخبار كرة القدم'
    : 'World Cup 2026 news, foot-boll, football news, tournament updates';
tpl('header');
?>

<div class="page-head">
  <h1>📰 <?= e(t('latest_news')) ?></h1>
  <p class="muted"><?= e(t('news_intro')) ?></p>
</div>

<?php if (!$items): ?>
  <p class="empty-note"><?= e(t('no_news')) ?></p>
<?php else: ?>
  <div class="fb-feed fb-feed-grid">
    <?php foreach ($items as $it) render_news_item($it, 'card'); ?>
  </div>
<?php endif; ?>

<?php tpl('footer'); ?>
