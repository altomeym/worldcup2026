<?php
/**
 * about.php — من نحن (محتوى أصلي — متطلب AdSense).
 */
require __DIR__ . '/includes/bootstrap.php';

$ar   = (current_lang() === 'ar');
$L    = fn(string $a, string $e) => $ar ? $a : $e;
$host = parse_url(SITE_URL, PHP_URL_HOST) ?: 'foot-boll.com';

$page_title = t('about_title');
$page_desc  = $L(
    'foot-boll منصّة عربية تحليلية مستقلة لكأس العالم 2026 — نبني طبقة أصلية فوق البيانات الرياضية: تحليلات FIFA، ملفات لاعبين، توقعات تفاعلية، وAPI للمطوّرين.',
    'foot-boll is an independent Arabic analytics platform for World Cup 2026 — we build an original layer on sports data: FIFA analytics, player profiles, interactive predictions, and a developer API.'
);
$page_keywords = $L(
    'foot-boll, من نحن, كأس العالم 2026, منصة تحليلية, محتوى أصلي',
    'foot-boll, about us, World Cup 2026, analytics platform, original content'
);

tpl('header');
?>

<div class="page-head">
  <h1><?= e($page_title) ?></h1>
  <p class="muted"><?= e($L('آخر تحديث: 19 يوليو 2026', 'Last updated: 19 July 2026')) ?></p>
</div>

<div class="prose" style="max-width:780px;margin:0 auto;line-height:2">

  <p><?= e($L(
    'foot-boll ليس موقع نتائج تقليدياً. نحن منصّة عربية مستقلة بُنيت خصيصاً لكأس العالم 2026 في كندا والمكسيك والولايات المتحدة، تجمع بين دقة البيانات الرياضية وطبقة تحليلية أصلية لا تقتصر على عرض النتيجة والوقت.',
    'foot-boll is not a traditional scores site. We are an independent Arabic platform built specifically for the 2026 World Cup in Canada, Mexico, and the USA — combining accurate sports data with an original analytics layer that goes far beyond showing the score and kick-off time.'
  )) ?></p>

  <h2><?= e($L('ما الذي نبنيه فوق البيانات العامة', 'What we build above public data')) ?></h2>
  <p><?= e($L(
    'جداول المباريات حقائق عامة قد تظهر في أي موقع. قيمة foot-boll تبدأ بعدها: مقالات Insights، قراءة أرقام FIFA، ملفات لاعبين، توقعات ترفيهية، سياق تحريري للأخبار، وتوقيت محلي للمشجّع العربي. شرحنا الفصل بين العام والأصلي في صفحة المنهجية.',
    'Match tables are public facts any site can show. foot-boll’s value starts after that: Insights essays, FIFA metric literacy, player profiles, entertainment predictions, editorial news context, and local kickoff times for Arabic fans. We explain the public-vs-original split on our methodology page.'
  )) ?></p>
  <p>
    <a href="<?= e(url('methodology.php')) ?>"><?= e($L('اقرأ منهجيتنا', 'Read our methodology')) ?></a>
    ·
    <a href="<?= e(url('insights.php')) ?>"><?= e($L('تحليلات foot-boll', 'foot-boll Insights')) ?></a>
    ·
    <a href="<?= e(url('contact.php')) ?>"><?= e($L('تواصل معنا', 'Contact us')) ?></a>
  </p>

  <h2><?= e($L('رسالتنا', 'Our mission')) ?></h2>
  <p><?= e($L(
    'نوفّر للمشجّع العربي مركزاً واحداً يجمع النتائج المحدّثة، التحليلات التقنية، التوقعات التفاعلية، وملفات اللاعبين — بلغة عربية وبتجربة مصمّمة لجمهورنا، لا منسوخة من قوالب جاهزة.',
    'We give Arabic-speaking fans a single hub for live scores, technical analytics, interactive predictions, and player profiles — in Arabic, with an experience designed for our audience, not copied from off-the-shelf templates.'
  )) ?></p>

  <h2><?= e($L('ما يميّزنا عن مواقع النتائج التقليدية', 'What sets us apart from score-only sites')) ?></h2>
  <ul>
    <li><?= e($L('تحليلات ما قبل وبعد المباراة مولّدة بعناية لكل لقاء — وليس مجرد جدول نتائج.', 'Carefully crafted pre- and post-match analysis for every fixture — not just a results table.')) ?></li>
    <li><?= e($L('تقارير FIFA التفصيلية: استحواذ، تسديدات، xG، وتمريرات حيث تتوفر البيانات.', 'Detailed FIFA reports: possession, shots, xG, and passes where data is available.')) ?></li>
    <li><?= e($L('ملفات لاعبين تقنية بتقييمات FIFA الرسمية وبيانات بدنية.', 'Technical player profiles with official FIFA ratings and physical data.')) ?></li>
    <li><?= e($L('لوحة إحصائيات تفاعلية، هدّافون، بطاقات، حكّام، ورجل المباراة.', 'Interactive stats dashboard, top scorers, bookings, referees, and player of the match.')) ?></li>
    <li><?= e($L('مسابقات توقع مجانية بنقاط وصدارة — ترفيه رياضي وليس مراهنة بمال حقيقي.', 'Free prediction games with points and leaderboards — sports entertainment, not real-money gambling.')) ?></li>
    <li><?= e($L('أخبار مع سياق تحريري يربط كل خبر بمنتخبات ومباريات البطولة على foot-boll.', 'News with editorial context linking each story to tournament teams and matches on foot-boll.')) ?></li>
    <li><?= e($L('واجهة API مجانية (JSON) للمطوّرين والمواقع الرياضية.', 'Free JSON API for developers and sports websites.')) ?></li>
  </ul>

  <h2><?= e($L('منهجية البيانات والمحتوى الأصلي', 'Data methodology and original content')) ?></h2>
  <p><?= e($L(
    'جدول المباريات والنتائج الأساسية من مصادر مفتوحة (openfootball). الإحصائيات المتقدمة وتقارير FIFA من بيانات رسمية حيث تُفعَّل. النصوص التحليلية والسياق التحريري يُنتَجان بواسطة فريق foot-boll ولا يُنسَخان من مواقع أخرى. عند استخدام عناوين أخبار خارجية، نضيف سياقاً أصلياً يربط الخبر ببطولة 2026 ونُحيل إلى المصدر.',
    'Core schedules and results come from open data (openfootball). Advanced stats and FIFA reports use official data where enabled. Analytical text and editorial context are produced by the foot-boll team and are not copied from other sites. When we use external news headlines, we add original context linking the story to the 2026 tournament and cite the source.'
  )) ?></p>

  <h2><?= e($L('الامتثال والشفافية', 'Compliance and transparency')) ?></h2>
  <p><?= e($L(
    'نحترم حقوق النشر للمحتوى الإخباري ونربط دائماً بالمصدر الأصلي. لا نقدّم مراهنة بمال حقيقي. بيانات المباريات حقائق رياضية عامة؛ القيمة التي نضيفها هي التحليل والتفاعل والتجربة العربية الأصيلة.',
    'We respect news publishers\' rights and always link to original sources. We do not offer real-money gambling. Match data is public sports fact; our added value is analysis, interactivity, and an authentic Arabic experience.'
  )) ?></p>

  <h2><?= e($L('تواصل معنا', 'Contact us')) ?></h2>
  <p><?= $ar
    ? 'للاستفسارات، الرعاية، والشراكات: <a href="mailto:' . e(CONTACT_EMAIL) . '">' . e(CONTACT_EMAIL) . '</a> — أو عبر صفحة <a href="' . e(url('contact.php')) . '">تواصل معنا</a>.'
    : 'For inquiries, sponsorship, and partnerships: <a href="mailto:' . e(CONTACT_EMAIL) . '">' . e(CONTACT_EMAIL) . '</a> — or via our <a href="' . e(url('contact.php')) . '">Contact</a> page.' ?></p>

  <p class="muted"><?= e($host) ?> · <?= e($L('أُعدّ بعناية لعشّاق كرة القدم العرب', 'Crafted for Arabic football fans')) ?></p>

</div>

<?php tpl('footer'); ?>
