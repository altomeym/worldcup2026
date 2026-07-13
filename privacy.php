<?php
/**
 * privacy.php — سياسة الخصوصية (عربي/إنجليزي).
 * محتوى أصلي يطابق ما يجمعه foot-boll فعلياً — مطلوب لـ AdSense وثقة الزوار.
 */
require __DIR__ . '/includes/bootstrap.php';

$ar   = (current_lang() === 'ar');
$L    = fn(string $a, string $e) => $ar ? $a : $e;
$host = parse_url(SITE_URL, PHP_URL_HOST) ?: 'foot-boll.com';

$page_title = $L('سياسة الخصوصية', 'Privacy Policy');
$page_desc  = $L(
    "كيف يتعامل {$host} مع بياناتك: الحسابات الاختيارية، الكوكيز، الإعلانات، تحليلات الذكاء الاصطناعي، وحقوقك.",
    "How {$host} handles your data: optional accounts, cookies, advertising, AI analytics, and your rights."
);
$page_keywords = $L(
    'سياسة الخصوصية, foot-boll, كوكيز, AdSense, بيانات شخصية',
    'privacy policy, foot-boll, cookies, AdSense, personal data'
);

tpl('header');
?>

<div class="page-head">
  <h1><?= e($page_title) ?></h1>
  <p class="muted"><?= e($L('آخر تحديث: يوليو 2026', 'Last updated: July 2026')) ?></p>
</div>

<div class="prose" style="max-width:780px;margin:0 auto;line-height:2">

  <p><?= e($L(
    "خصوصيتك تهمنا. توضّح هذه الصفحة ما يجمعه موقع {$host} عند استخدامك له، ولماذا، وكيف نحمي بياناتك، وما حقوقك عليها. نحن منصّة تحليلية عربية مستقلة — وليس موقع مراهنات.",
    "Your privacy matters to us. This page explains what {$host} collects when you use it, why, how we protect your data, and your rights. We are an independent Arabic analytics platform — not a gambling site."
  )) ?></p>

  <h2><?= e($L('١. من نحن', '1. Who we are')) ?></h2>
  <p><?= $ar
    ? "مشغّل الموقع هو فريق foot-boll. للاستفسارات المتعلقة بالخصوصية: <a href=\"mailto:" . e(CONTACT_EMAIL) . "\">" . e(CONTACT_EMAIL) . "</a>."
    : "The site is operated by the foot-boll team. For privacy inquiries: <a href=\"mailto:" . e(CONTACT_EMAIL) . "\">" . e(CONTACT_EMAIL) . "</a>." ?></p>

  <h2><?= e($L('٢. البيانات التي نجمعها', '2. Data we collect')) ?></h2>
  <ul>
    <li><?= e($L(
      'بيانات الحساب (اختيارية — فقط عند التسجيل لمسابقات التوقعات): اسم مستخدم، اسم عرض، بريد إلكتروني، رقم هاتف، ورمز الدولة.',
      'Account data (optional — only when you register for prediction games): username, display name, email, phone number, and country code.'
    )) ?></li>
    <li><?= e($L(
      'نشاط الألعاب: توقعات المباريات وإجابات سؤال اليوم — لاحتساب النقاط والصدارة فقط.',
      'Game activity: match predictions and daily trivia answers — used solely to compute points and leaderboards.'
    )) ?></li>
    <li><?= e($L(
      'رسائل نموذج التواصل: الاسم والبريد ونص الرسالة — لنرد عليك فقط، ولا تُستخدم للتسويق.',
      'Contact form messages: name, email, and message text — used only to reply to you, never for marketing.'
    )) ?></li>
    <li><?= e($L(
      'بيانات تقنية: عنوان IP يُستخدم مؤقتاً للحماية من إساءة الاستخدام (حدود المعدّل) وعدّاد زوار مجهول الهوية.',
      'Technical data: IP address used temporarily for abuse protection (rate limiting) and an anonymous visitor counter.'
    )) ?></li>
    <li><?= e($L(
      'تحليلات المباريات بالذكاء الاصطناعي: نولّد نصوص معاينة/ملخّص من معطيات المباراة العامة فقط — لا نجمع بيانات شخصية إضافية لهذا الغرض.',
      'AI match analysis: we generate preview/summary text from public match facts only — no extra personal data is collected for this.'
    )) ?></li>
  </ul>
  <p class="muted"><?= e($L(
    'يمكنك تصفّح معظم الموقع دون إنشاء حساب. لا نطلب بيانات حسّاسة (بطاقات بنكية، هوية وطنية، موقع GPS).',
    'You can browse most of the site without an account. We do not request sensitive data (bank cards, national ID, GPS location).'
  )) ?></p>

  <h2><?= e($L('٣. الكوكيز والتخزين المحلي', '3. Cookies & local storage')) ?></h2>
  <ul>
    <li><?= e($L(
      'كوكي الجلسة: يُنشأ فقط عند تسجيل الدخول — الزوار بلا حساب لا يحصلون على كوكي جلسة.',
      'Session cookie: created only when you sign in — visitors without an account receive no session cookie.'
    )) ?></li>
    <li><?= e($L(
      'كوكي اللغة: يحفظ لغتك المفضلة (عربي/إنجليزي/فرنسي).',
      'Language cookie: remembers your preferred language (Arabic/English/French).'
    )) ?></li>
    <li><?= e($L(
      'كوكي حماية النماذج (CSRF): يمنع إرسال النماذج المزوّرة.',
      'Form-protection (CSRF) cookie: prevents forged form submissions.'
    )) ?></li>
    <li><?= e($L(
      'التخزين المحلي في متصفحك: تفضيلات تذكير المباريات وتثبيت التطبيق — تبقى على جهازك ولا تُرسل إلينا.',
      'Browser local storage: match-reminder and app-install preferences — kept on your device, never sent to us.'
    )) ?></li>
  </ul>

  <h2><?= e($L('٤. الإعلانات (Google AdSense)', '4. Advertising (Google AdSense)')) ?></h2>
  <p><?= e($L(
    'نعرض إعلانات عبر Google AdSense. تستخدم جوجل وشركاؤها (كطرف ثالث) كوكيز لعرض إعلانات قد تناسب اهتماماتك بناءً على زياراتك لهذا الموقع ومواقع أخرى.',
    'We display ads via Google AdSense. Google and its partners (as third parties) use cookies to serve ads that may match your interests based on visits to this and other websites.'
  )) ?></p>
  <ul>
    <li><?= $ar
      ? 'يمكنك إيقاف الإعلانات المخصّصة من <a href="https://www.google.com/settings/ads" target="_blank" rel="noopener">إعدادات إعلانات جوجل</a>.'
      : 'You can opt out of personalised advertising in <a href="https://www.google.com/settings/ads" target="_blank" rel="noopener">Google Ads Settings</a>.' ?></li>
    <li><?= $ar
      ? 'أو عبر <a href="https://www.aboutads.info" target="_blank" rel="noopener">aboutads.info</a> لإلغاء كوكيز شبكات إعلانية أخرى.'
      : 'Or via <a href="https://www.aboutads.info" target="_blank" rel="noopener">aboutads.info</a> to opt out of other ad networks’ cookies.' ?></li>
  </ul>

  <h2><?= e($L('٥. خدمات الطرف الثالث', '5. Third-party services')) ?></h2>
  <p><?= e($L(
    'تُحمَّل بعض الموارد من خدمات خارجية: خطوط جوجل (Google Fonts)، صور الأعلام (flagcdn.com)، بيانات المباريات من مصادر عامة مفتوحة، وروابط بث/فيديو رسمية. قد تسجّل هذه الخدمات عنوان IP الخاص بك وفق سياساتها الخاصة.',
    'Some resources load from external services: Google Fonts, flag images (flagcdn.com), match data from open public sources, and official streaming/video links. These services may log your IP address per their own policies.'
  )) ?></p>

  <h2><?= e($L('٦. النشرة البريدية', '6. Email digest')) ?></h2>
  <p><?= e($L(
    'إن سجّلت حساباً فقد تصلك نشرة دورية بمستجدات البطولة ونقاطك. كل رسالة تتضمن رابط إلغاء اشتراك يعمل بنقرة واحدة.',
    'If you register an account you may receive a periodic digest with tournament updates and your points. Every email includes a one-click unsubscribe link.'
  )) ?></p>

  <h2><?= e($L('٧. حماية بياناتك', '7. How we protect your data')) ?></h2>
  <ul>
    <li><?= e($L('كلمات السر تُخزَّن مشفّرة بخوارزمية bcrypt ولا يمكن استرجاعها كنص.',
                 'Passwords are stored hashed with bcrypt and can never be read back as text.')) ?></li>
    <li><?= e($L('الموقع يُقدَّم عبر HTTPS المشفّر في الإنتاج.',
                 'The site is served over encrypted HTTPS in production.')) ?></li>
    <li><?= e($L('لا نبيع بياناتك ولا نشاركها مع أي جهة لأغراض تسويقية.',
                 'We never sell your data nor share it with anyone for marketing purposes.')) ?></li>
  </ul>

  <h2><?= e($L('٨. حقوقك', '8. Your rights')) ?></h2>
  <p><?= $ar
    ? 'يحق لك طلب الاطلاع على بياناتك أو تصحيحها أو حذف حسابك نهائياً. راسلنا على <a href="mailto:' . e(CONTACT_EMAIL) . '">' . e(CONTACT_EMAIL) . '</a> وسنستجيب خلال أيام عمل معدودة.'
    : 'You may request access to your data, correct it, or permanently delete your account. Email us at <a href="mailto:' . e(CONTACT_EMAIL) . '">' . e(CONTACT_EMAIL) . '</a> and we will respond within a few business days.' ?></p>

  <h2><?= e($L('٩. تحديثات هذه السياسة', '9. Changes to this policy')) ?></h2>
  <p><?= e($L(
    'قد نحدّث هذه الصفحة عند إضافة ميزات جديدة. يُذكر تاريخ آخر تحديث أعلاها دائماً. للأسئلة العامة راجع أيضاً ',
    'We may update this page when new features are added. The “last updated” date above always reflects the latest revision. For general questions see also '
  )) ?><a href="<?= e(url('faq.php')) ?>"><?= e($L('الأسئلة الشائعة', 'FAQ')) ?></a><?= e($L('.', '.')) ?></p>

  <p class="muted"><?= e($host) ?> · <?= e($L('أُعدّ بعناية لعشّاق كرة القدم العرب', 'Crafted for Arabic football fans')) ?></p>

</div>

<?php tpl('footer'); ?>
