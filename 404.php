<?php
/**
 * 404.php — صفحة «غير موجود» حقيقية لمحركات البحث والزائر.
 * تُستدعى أيضاً عبر ErrorDocument في .htaccess.
 */
require __DIR__ . '/includes/bootstrap.php';
http_response_code(404);

$ar = (current_lang() === 'ar');
$L  = fn(string $a, string $e) => $ar ? $a : $e;

$page_title  = $L('الصفحة غير موجودة', 'Page not found');
$page_desc   = $L(
    'الصفحة التي طلبتها غير موجودة على foot-boll.',
    'The page you requested was not found on foot-boll.'
);
$page_robots = 'noindex,follow';

tpl('header');
?>

<div class="page-head" style="text-align:center;padding:48px 16px 24px">
  <p class="muted" style="font-size:3rem;line-height:1;margin:0 0 8px;font-weight:700">404</p>
  <h1><?= e($page_title) ?></h1>
  <p class="muted"><?= e($L(
      'الرابط قد يكون قديماً أو خاطئاً. جرّب إحدى الصفحات التالية:',
      'This link may be outdated or incorrect. Try one of these pages:'
  )) ?></p>
</div>

<p style="text-align:center;display:flex;flex-wrap:wrap;gap:10px;justify-content:center;margin:8px 0 40px">
  <a class="btn btn-accent" href="<?= e(url('index.php')) ?>"><?= e($L('الرئيسية', 'Home')) ?></a>
  <a class="btn" href="<?= e(url('matches.php')) ?>"><?= e(t('matches')) ?></a>
  <a class="btn" href="<?= e(url('news.php')) ?>"><?= e(t('news')) ?></a>
  <a class="btn" href="<?= e(url('predict.php')) ?>"><?= e(t('predict')) ?></a>
</p>

<?php tpl('footer'); ?>
