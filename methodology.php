<?php
/**
 * methodology.php — منهجية foot-boll (محتوى أصلي لتميّز AdSense).
 */
require __DIR__ . '/includes/bootstrap.php';

$ar   = (current_lang() === 'ar');
$L    = fn(string $a, string $e) => $ar ? $a : $e;
$host = parse_url(SITE_URL, PHP_URL_HOST) ?: 'foot-boll.com';

$page_title = $L('منهجية foot-boll', 'foot-boll methodology');
$page_desc  = $L(
    'كيف نبني foot-boll فوق البيانات العامة: مصادر الجداول، طبقة FIFA، الأخبار، التوقعات، وما هو أصلي على المنصّة.',
    'How we build foot-boll on top of public data: schedule sources, FIFA layer, news, predictions, and what is original on the platform.'
);
$page_keywords = $L(
    'منهجية, foot-boll, مصادر البيانات, محتوى أصلي, كأس العالم 2026',
    'methodology, foot-boll, data sources, original content, World Cup 2026'
);

tpl('header');
?>

<div class="page-head">
  <h1><?= e($page_title) ?></h1>
  <p class="muted"><?= e($L('آخر تحديث: 19 يوليو 2026', 'Last updated: 19 July 2026')) ?></p>
</div>

<div class="prose" style="max-width:780px;margin:0 auto;line-height:2">

  <p><?= e($L(
    'هذه الصفحة تشرح بصراحة ما هو عام وما هو أصلي على foot-boll. نكتبها لأن المشجّع — ومراجعي السياسات — يستحقون معرفة كيف تُصنع المنصّة.',
    'This page explains plainly what is public and what is original on foot-boll. We write it because fans — and policy reviewers — deserve to know how the platform is built.'
  )) ?></p>

  <h2><?= e($L('١. ما هو عام (حقائق رياضية مشتركة)', '1. What is public (shared sports facts)')) ?></h2>
  <ul>
    <li><?= e($L(
      'جداول المباريات والنتائج الأساسية من مصادر مفتوحة (openfootball) — ملك عام ومتاحة لأي موقع.',
      'Core fixtures and results from open sources (openfootball) — public domain and available to any site.'
    )) ?></li>
    <li><?= e($L(
      'أسماء المنتخبات، الملاعب، والجولات حقائق بطولة رسمية لا يملكها أحد حصرياً.',
      'Team names, venues, and rounds are official tournament facts that nobody owns exclusively.'
    )) ?></li>
    <li><?= e($L(
      'لذلك قد ترى جداول مشابهة في مواقع أخرى — التشابه هنا طبيعي، وليس نسخاً لمحتوى تحريري.',
      'That is why you may see similar tables elsewhere — that overlap is normal, not a copy of editorial content.'
    )) ?></li>
  </ul>

  <h2><?= e($L('٢. ما يبنيه foot-boll فوق البيانات', '2. What foot-boll builds on top of the data')) ?></h2>
  <ul>
    <li><?= e($L(
      'مقالات تحليلية أصلية في قسم Insights — مكتوبة لقرّاء عرب عن القوانين، الأرقام، والتوقعات.',
      'Original analytical essays in Insights — written for Arabic readers on rules, metrics, and predictions.'
    )) ?></li>
    <li><?= e($L(
      'طبقة FIFA: تقارير مفصّلة، ملفات لاعبين، بيانات بدنية، ولوحة إحصائيات تفاعلية حيث تتوفر البيانات.',
      'FIFA layer: detailed reports, player profiles, physical data, and an interactive stats dashboard where data is available.'
    )) ?></li>
    <li><?= e($L(
      'توقيت محلي تلقائي لكل مباراة — حاسم للمشجّع في الخليج والمغرب العربي أمام فروق أمريكا الشمالية.',
      'Automatic local kickoff times — critical for fans in the Gulf and Maghreb facing North American time gaps.'
    )) ?></li>
    <li><?= e($L(
      'مسابقات توقع وصدارة ودوريات خاصة — ترفيه رياضي مجاني بلا مال حقيقي.',
      'Prediction games, leaderboards, and private leagues — free sports entertainment with no real money.'
    )) ?></li>
    <li><?= e($L(
      'سياق تحريري قصير لأخبار RSS دون إعادة نشر المقال الكامل — احترام لحقوق الناشرين.',
      'Short editorial context for RSS headlines without republishing full articles — respecting publisher rights.'
    )) ?></li>
  </ul>

  <h2><?= e($L('٣. سياسة الأخبار', '3. News policy')) ?></h2>
  <p><?= e($L(
    'لا نعيد كتابة مقالات الوكالات كاملة. نعرض العنوان مع ملخّص/سياق يربط الخبر بالبطولة على foot-boll، ونوجّه القارئ إلى المصدر الأصلي للقراءة الكاملة. هذا يحمي حقوق النشر ويعطي قيمة واضحة لزائرنا.',
    'We do not republish agency articles in full. We show the headline with a short context linking the story to the tournament on foot-boll, and send readers to the original source for the full piece. That protects copyright and adds clear value for our visitors.'
  )) ?></p>

  <h2><?= e($L('٤. الذكاء الاصطناعي', '4. Artificial intelligence')) ?></h2>
  <p><?= e($L(
    'نصوص معاينة/ملخّص المباراة تُولَّد من معطيات عامة متاحة فقط، وتُخزَّن بعد التوليد. لا نختلق إصابات أو تشكيلات. اعتبرها مساعدة تحريرية — راجع دائماً الأرقام الرسمية عند الجدل.',
    'Match preview/summary text is generated from available public facts only and cached after generation. We do not invent injuries or lineups. Treat it as editorial assistance — always cross-check official numbers when it matters.'
  )) ?></p>

  <h2><?= e($L('٥. ما لسنا عليه', '5. What we are not')) ?></h2>
  <ul>
    <li><?= e($L('لسنا موقع مراهنات ولا نبيع نقاطاً نقدية.', 'We are not a betting site and do not sell cashable points.')) ?></li>
    <li><?= e($L('لا نستضيف بثاً غير مرخّص — نربط بمصادر رسمية فقط.', 'We do not host unlicensed streams — we link to official sources only.')) ?></li>
    <li><?= e($L('لا ندّعي ملكية جدول البطولة العام.', 'We do not claim ownership of the public tournament schedule.')) ?></li>
  </ul>

  <h2><?= e($L('٦. أين تقرأ المزيد', '6. Where to read more')) ?></h2>
  <p>
    <a href="<?= e(url('insights.php')) ?>"><?= e($L('تحليلات foot-boll', 'foot-boll Insights')) ?></a>
    ·
    <a href="<?= e(url('faq.php')) ?>"><?= e($L('الأسئلة الشائعة', 'FAQ')) ?></a>
    ·
    <a href="<?= e(url('about.php')) ?>"><?= e(t('about_title')) ?></a>
    ·
    <a href="<?= e(url('privacy.php')) ?>"><?= e($L('سياسة الخصوصية', 'Privacy Policy')) ?></a>
    ·
    <a href="<?= e(url('contact.php')) ?>"><?= e($L('تواصل معنا', 'Contact')) ?></a>
  </p>

  <p class="muted"><?= e($host) ?> · <?= e($L('شفافية أولاً — ثقة المشجّع أهم من تضخيم الأرقام', 'Transparency first — fan trust matters more than inflated metrics')) ?></p>

</div>

<?php tpl('footer'); ?>
