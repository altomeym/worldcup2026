<?php
/**
 * about.php — من نحن (محتوى أصلي — متطلب AdSense).
 */
require __DIR__ . '/includes/bootstrap.php';

$ar = (current_lang() === 'ar');
$L  = fn(string $a, string $e) => $ar ? $a : $e;
$host = parse_url(SITE_URL, PHP_URL_HOST) ?: 'foot-boll.com';

$page_title = t('about_title');
$page_desc  = $L(
    'تعرّف على foot-boll.com — منصّة تحليلية مستقلّة لكأس العالم 2026: نتائج، إحصائيات FIFA، توقعات مجانية، وAPI — بدون بث مباشر.',
    'About foot-boll.com — an independent World Cup 2026 analytics hub: scores, FIFA stats, free predictions, and API — no live streaming.'
);

tpl('header');
?>

<div class="page-head">
  <h1><?= e($page_title) ?></h1>
  <p class="muted"><?= e($L('آخر تحديث: يوليو 2026', 'Last updated: July 2026')) ?></p>
</div>

<div class="prose" style="max-width:780px;margin:0 auto;line-height:2">

  <p><?= e($L(
    'foot-boll.com منصّة تحليلية مستقلّة لكأس العالم 2026 (كندا · المكسيك · الولايات المتحدة). نقدّم تجربة غنية بالأرقام والتفاعل — لا نبث المباريات ولا ننشر مقالات إخبارية كاملة منسوخة.',
    'foot-boll.com is an independent analytics platform for the FIFA World Cup 2026 (Canada · Mexico · USA). We focus on numbers, interactivity, and clarity — we do not stream matches or republish full news articles.'
  )) ?></p>

  <h2><?= e($L('ماذا نقدّم', 'What we offer')) ?></h2>
  <ul>
    <li><?= e($L('جدول المباريات والنتائج المحدّثة من مصادر مفتوحة (openfootball).', 'Match schedule and updated results from open data (openfootball).')) ?></li>
    <li><?= e($L('ملفات لاعبين تقنية بتقييمات وبيانات FIFA الرسمية حيث تتوفر.', 'Technical player profiles with official FIFA ratings and metrics where available.')) ?></li>
    <li><?= e($L('لوحة إحصائيات، بيانات بدنية، هدّافون، بطاقات، وحكّام.', 'Stats dashboard, physical data, top scorers, cards, and referees.')) ?></li>
    <li><?= e($L('مسابقات توقع مجانية — نقاط وصدارة، وليست مراهنة بمال حقيقي.', 'Free prediction games with points and leaderboards — not real-money gambling.')) ?></li>
    <li><?= e($L('واجهة API مجانية (JSON) للمطوّرين.', 'Free JSON API for developers.')) ?></li>
  </ul>

  <h2><?= e($L('ما لا نقدّمه', 'What we do not offer')) ?></h2>
  <ul>
    <li><?= e($L('لا بث مباشر للمباريات — للمشاهدة راجع القنوات الرسمية أو FIFA.com.', 'No live match streaming — for viewing, use official broadcasters or FIFA.com.')) ?></li>
    <li><?= e($L('لا نعيد نشر مقالات RSS كاملة — نعرض عناوين وملخّصات مع رابط للمصدر.', 'No full republication of RSS articles — we show headlines and snippets with a link to the source.')) ?></li>
  </ul>

  <h2><?= e($L('مصادر البيانات', 'Data sources')) ?></h2>
  <p><?= e($L(
    'جدول المباريات والنتائج الأساسية من openfootball (ملك عام). إحصائيات المباريات المتقدمة من بيانات FIFA حيث تُفعَّل. الأخبار: عناوين من Bing/Google News مع رابط مباشر للناشر.',
    'Core schedule and results from openfootball (public domain). Advanced match metrics from FIFA data where enabled. News: headlines from Bing/Google News with direct links to publishers.'
  )) ?></p>

  <h2><?= e($L('تواصل', 'Contact')) ?></h2>
  <p><?= $ar
    ? 'للاستفسارات والرعاية: <a href="mailto:' . e(CONTACT_EMAIL) . '">' . e(CONTACT_EMAIL) . '</a> — أو استخدم نموذج «تواصل معنا» في أسفل أي صفحة.'
    : 'For inquiries and sponsorship: <a href="mailto:' . e(CONTACT_EMAIL) . '">' . e(CONTACT_EMAIL) . '</a> — or use the contact form in the site footer.' ?></p>

  <p class="muted"><?= e($host) ?> · <?= e($L('صُنع بشغف لكرة القدم', 'Built with love for football')) ?></p>

</div>

<?php tpl('footer'); ?>
