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
    'تعرّف على foot-boll.com — منصّة مستقلّة لكأس العالم 2026: نتائج، روابط بث رسمية، إحصائيات FIFA، توقعات مجانية، وAPI.',
    'About foot-boll.com — an independent World Cup 2026 hub: scores, official watch links, FIFA stats, free predictions, and API.'
);

tpl('header');
?>

<div class="page-head">
  <h1><?= e($page_title) ?></h1>
  <p class="muted"><?= e($L('آخر تحديث: يوليو 2026', 'Last updated: July 2026')) ?></p>
</div>

<div class="prose" style="max-width:780px;margin:0 auto;line-height:2">

  <p><?= e($L(
    'foot-boll.com منصّة مستقلّة لكأس العالم 2026 (كندا · المكسيك · الولايات المتحدة). نقدّم تجربة غنية بالأرقام والتفاعل مع روابط بث رسمية لكل مباراة.',
    'foot-boll.com is an independent platform for the FIFA World Cup 2026 (Canada · Mexico · USA). We focus on numbers, interactivity, and official watch links for every match.'
  )) ?></p>

  <h2><?= e($L('ماذا نقدّم', 'What we offer')) ?></h2>
  <ul>
    <li><?= e($L('روابط بث رسمية (beIN / TOD / FIFA+) على صفحة كل مباراة.', 'Official watch links (beIN / TOD / FIFA+) on every match page.')) ?></li>
    <li><?= e($L('جدول المباريات والنتائج المحدّثة من مصادر مفتوحة (openfootball).', 'Match schedule and updated results from open data (openfootball).')) ?></li>
    <li><?= e($L('ملفات لاعبين تقنية بتقييمات وبيانات FIFA الرسمية حيث تتوفر.', 'Technical player profiles with official FIFA ratings and metrics where available.')) ?></li>
    <li><?= e($L('لوحة إحصائيات، بيانات بدنية، هدّافون، بطاقات، وحكّام.', 'Stats dashboard, physical data, top scorers, cards, and referees.')) ?></li>
    <li><?= e($L('مسابقات توقع مجانية — نقاط وصدارة، وليست مراهنة بمال حقيقي.', 'Free prediction games with points and leaderboards — not real-money gambling.')) ?></li>
    <li><?= e($L('واجهة API مجانية (JSON) للمطورين.', 'Free JSON API for developers.')) ?></li>
    <li><?= e($L('أخبار مع سياق تحريري يربط كل خبر بمنتخبات ومباريات البطولة.', 'News with editorial context linking each story to tournament teams and matches.')) ?></li>
  </ul>

  <h2><?= e($L('مصادر البيانات', 'Data sources')) ?></h2>
  <p><?= e($L(
    'جدول المباريات والنتائج الأساسية من openfootball (ملك عام). إحصائيات المباريات المتقدمة من بيانات FIFA حيث تُفعَّل. الأخبار: عناوين مختارة مع سياق foot-boll.com ورابط للمصدر.',
    'Core schedule and results from openfootball (public domain). Advanced match metrics from FIFA data where enabled. News: curated headlines with foot-boll.com context and links to publishers.'
  )) ?></p>

  <h2><?= e($L('تواصل', 'Contact')) ?></h2>
  <p><?= $ar
    ? 'للاستفسارات والرعاية: <a href="mailto:' . e(CONTACT_EMAIL) . '">' . e(CONTACT_EMAIL) . '</a> — أو استخدم نموذج «تواصل معنا» في أسفل أي صفحة.'
    : 'For inquiries and sponsorship: <a href="mailto:' . e(CONTACT_EMAIL) . '">' . e(CONTACT_EMAIL) . '</a> — or use the contact form in the site footer.' ?></p>

  <p class="muted"><?= e($host) ?> · <?= e($L('صُنع بشغف لكرة القدم', 'Built with love for football')) ?></p>

</div>

<?php tpl('footer'); ?>
