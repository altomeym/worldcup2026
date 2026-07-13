<?php
/**
 * faq.php — أسئلة شائعة ومنهجية foot-boll (محتوى أصلي).
 */
require __DIR__ . '/includes/bootstrap.php';

$ar   = (current_lang() === 'ar');
$L    = fn(string $a, string $e) => $ar ? $a : $e;
$host = parse_url(SITE_URL, PHP_URL_HOST) ?: 'foot-boll.com';

$page_title = $L('الأسئلة الشائعة', 'Frequently Asked Questions');
$page_desc  = $L(
    "إجابات عن foot-boll وكأس العالم 2026: مصادر البيانات، التحليلات، التوقعات، والذكاء الاصطناعي — {$host}.",
    "Answers about foot-boll and World Cup 2026: data sources, analytics, predictions, and AI — {$host}."
);
$page_keywords = $L(
    'أسئلة شائعة, foot-boll, كأس العالم 2026, منهجية البيانات, FAQ',
    'FAQ, foot-boll, World Cup 2026, data methodology'
);

tpl('header');
?>

<div class="page-head">
  <h1><?= e($page_title) ?></h1>
  <p class="muted"><?= e($L('آخر تحديث: يوليو 2026', 'Last updated: July 2026')) ?></p>
</div>

<div class="prose faq-page" style="max-width:780px;margin:0 auto;line-height:2">

  <p><?= e($L(
    "هذه الصفحة تجيب على الأسئلة الأكثر شيوعاً حول {$host} — من أين تأتي البيانات، وكيف نختلف عن مواقع النتائج التقليدية، وما الذي يمكنك توقعه من المنصّة.",
    "This page answers the most common questions about {$host} — where data comes from, how we differ from traditional score sites, and what you can expect from the platform."
  )) ?></p>

  <h2><?= e($L('عن الموقع', 'About the site')) ?></h2>

  <details class="faq-item">
    <summary><?= e($L('ما هو foot-boll؟', 'What is foot-boll?')) ?></summary>
    <p><?= e($L(
      'منصّة عربية تحليلية مستقلة لكأس العالم 2026. نجمع النتائج والجداول مع طبقة أصلية: تحليلات FIFA، ملفات لاعبين، توقعات تفاعلية، ونصوص ذكاء اصطناعي لكل مباراة — وليس مجرد جدول نتائج.',
      'An independent Arabic analytics platform for World Cup 2026. We combine schedules and scores with an original layer: FIFA analytics, player profiles, interactive predictions, and AI text for each match — not just a results table.'
    )) ?></p>
  </details>

  <details class="faq-item">
    <summary><?= e($L('هل foot-boll موقع مراهنات؟', 'Is foot-boll a betting site?')) ?></summary>
    <p><?= e($L(
      'لا. مسابقات التوقعات مجانية للترفيه الرياضي فقط. النقاط والصدارة ليست أموالاً حقيقية ولا قابلة للصرف.',
      'No. Prediction games are free sports entertainment only. Points and leaderboards are not real money and cannot be cashed out.'
    )) ?></p>
  </details>

  <details class="faq-item">
    <summary><?= e($L('هل أحتاج حساباً لاستخدام الموقع؟', 'Do I need an account?')) ?></summary>
    <p><?= e($L(
      'لا. يمكنك تصفّح النتائج والإحصائيات والتحليلات دون تسجيل. الحساب مطلوب فقط للمشاركة في التوقعات والصدارة وحفظ نقاطك.',
      'No. You can browse scores, stats, and analysis without signing up. An account is only needed to play predictions, appear on the leaderboard, and save your points.'
    )) ?></p>
  </details>

  <h2><?= e($L('البيانات والمنهجية', 'Data & methodology')) ?></h2>

  <details class="faq-item">
    <summary><?= e($L('من أين تأتي نتائج المباريات؟', 'Where do match results come from?')) ?></summary>
    <p><?= e($L(
      'الجدول والنتائج الأساسية من مصادر مفتوحة (openfootball). نحدّثها دورياً عبر أنظمة آلية. الإحصائيات المتقدمة وتقارير FIFA من بيانات رسمية حيث تُفعَّل.',
      'Core schedules and results come from open data (openfootball), refreshed periodically via automated systems. Advanced stats and FIFA reports use official data where enabled.'
    )) ?></p>
  </details>

  <details class="faq-item">
    <summary><?= e($L('لماذا تختلف بعض الأرقام عن مواقع أخرى؟', 'Why do some numbers differ from other sites?')) ?></summary>
    <p><?= e($L(
      'مصادر مختلفة، توقيت التحديث، وتعريفات الإحصائية (مثلاً التسديدات على المرمى) قد تختلف بين المزوّدين. نعرض مصدرنا ونحدّث عند تصحيح البيانات.',
      'Different sources, update timing, and stat definitions (e.g. shots on target) can vary between providers. We show our source and update when data is corrected.'
    )) ?></p>
  </details>

  <details class="faq-item">
    <summary><?= e($L('ما الذي يضيفه foot-boll فوق البيانات الخام؟', 'What does foot-boll add beyond raw data?')) ?></summary>
    <p><?= e($L(
      'تحليلات ما قبل وبعد المباراة، تقارير FIFA التفصيلية، ملفات لاعبين، لوحة إحصائيات تفاعلية، سياق تحريري للأخبار، وواجهة API للمطوّرين — طبقة تحليلية عربية لا تقتصر على عرض النتيجة.',
      'Pre- and post-match analysis, detailed FIFA reports, player profiles, an interactive stats dashboard, editorial context for news, and a developer API — an Arabic analytics layer beyond the scoreline.'
    )) ?></p>
  </details>

  <details class="faq-item">
    <summary><?= e($L('كيف يعمل تحليل الذكاء الاصطناعي؟', 'How does AI analysis work?')) ?></summary>
    <p><?= e($L(
      'نولّد معاينة قبل المباراة وملخّصاً بعدها من معطيات المباراة المتاحة فقط — دون اختلاق تشكيلات أو إصابات. النص يُخزَّن بعد التوليد الأول لتقليل التكلفة. قد يحتوي أخطاء؛ راجع التحليل مع البيانات الرسمية.',
      'We generate a pre-match preview and post-match summary from available match facts only — without inventing lineups or injuries. Text is cached after first generation. It may contain errors; cross-check with official data.'
    )) ?></p>
  </details>

  <h2><?= e($L('الميزات والاستخدام', 'Features & usage')) ?></h2>

  <details class="faq-item">
    <summary><?= e($L('كيف أتابع البث المباشر؟', 'How do I watch live?')) ?></summary>
    <p><?= e($L(
      'صفحة كل مباراة تعرض روابط رسمية لحاملي حقوق البث (beIN / TOD / FIFA+ حسب المنطقة). البث الكامل في MENA قد يتطلّب اشتراكاً؛ FIFA+ يوفّر highlights مجانية.',
      'Each match page shows official broadcaster links (beIN / TOD / FIFA+ depending on region). Full coverage in MENA may require a subscription; FIFA+ offers free highlights.'
    )) ?></p>
  </details>

  <details class="faq-item">
    <summary><?= e($L('هل يوجد تطبيق للجوال؟', 'Is there a mobile app?')) ?></summary>
    <p><?= e($L(
      'يمكنك تثبيت الموقع كتطبيق (PWA) من صفحة «ثبّت التطبيق» — بدون متجر، بأقل من 500 كيلوبايت، مع تنبيهات اختيارية قبل المباريات.',
      'You can install the site as an app (PWA) from the “Install app” page — no store, under 500KB, with optional pre-match notifications.'
    )) ?></p>
  </details>

  <details class="faq-item">
    <summary><?= e($L('هل يمكنني استخدام بياناتكم في موقعي؟', 'Can I use your data on my site?')) ?></summary>
    <p><?= e($L(
      'نعم — نوفّر واجهة API مجانية (JSON) للمطوّرين. راجع صفحة API للتوثيق وحدود الاستخدام.',
      'Yes — we offer a free JSON API for developers. See the API page for documentation and usage limits.'
    )) ?></p>
  </details>

  <h2><?= e($L('الخصوصية والإعلانات', 'Privacy & ads')) ?></h2>

  <details class="faq-item">
    <summary><?= e($L('هل تبيعون بياناتي؟', 'Do you sell my data?')) ?></summary>
    <p><?= e($L(
      'لا. لا نبيع بياناتك ولا نشاركها لأغراض تسويقية. التفاصيل الكاملة في ',
      'No. We never sell your data or share it for marketing. Full details in '
    )) ?><a href="<?= e(url('privacy.php')) ?>"><?= e($L('سياسة الخصوصية', 'Privacy Policy')) ?></a><?= e($L('.', '.')) ?></p>
  </details>

  <details class="faq-item">
    <summary><?= e($L('لماذا أرى إعلانات؟', 'Why do I see ads?')) ?></summary>
    <p><?= e($L(
      'الإعلانات عبر Google AdSense تساعدنا في تغطية تكاليف التشغيل. يمكنك إيقاف الإعلانات المخصّصة من إعدادات جوجل.',
      'Ads via Google AdSense help cover running costs. You can opt out of personalised ads in Google settings.'
    )) ?></p>
  </details>

  <p><?= e($L('لم تجد إجابتك؟ راجع ', 'Didn’t find your answer? See ')) ?><a href="<?= e(url('about.php')) ?>"><?= e(t('about_title')) ?></a><?= e($L(' أو راسلنا على ', ' or email us at ')) ?><a href="mailto:<?= e(CONTACT_EMAIL) ?>"><?= e(CONTACT_EMAIL) ?></a>.</p>

</div>

<?php tpl('footer'); ?>
