<?php
/**
 * privacy.php — سياسة الخصوصية (عربي/إنجليزي).
 * صفحة ثابتة مطلوبة لاعتماد Google AdSense ولثقة الزوار.
 * المحتوى يطابق ما يجمعه الموقع فعلياً — حدّثه إذا أضفت ميزة تجمع بيانات جديدة.
 */
require __DIR__ . '/includes/bootstrap.php';

$ar = (current_lang() === 'ar');
$L  = fn(string $a, string $e) => $ar ? $a : $e;

$page_title = $L('سياسة الخصوصية', 'Privacy Policy');
$page_desc  = $L('كيف يتعامل موقع wcup2026.org مع بياناتك: الحسابات، الكوكيز، الإعلانات، وحقوقك.',
                 'How wcup2026.org handles your data: accounts, cookies, advertising, and your rights.');

tpl('header');
?>

<div class="page-head">
  <h1><?= e($page_title) ?></h1>
  <p class="muted"><?= e($L('آخر تحديث: 11 يونيو 2026', 'Last updated: June 11, 2026')) ?></p>
</div>

<div class="prose" style="max-width:780px;margin:0 auto;line-height:2">

  <p><?= e($L(
    'خصوصيتك تهمنا. توضح هذه الصفحة ما نجمعه من بيانات عند استخدامك موقع wcup2026.org ولماذا، وكيف نحميها، وما حقوقك عليها.',
    'Your privacy matters to us. This page explains what data wcup2026.org collects, why, how it is protected, and your rights over it.')) ?></p>

  <h2><?= e($L('١. البيانات التي نجمعها', '1. Data we collect')) ?></h2>
  <ul>
    <li><?= e($L(
      'بيانات الحساب (اختيارية — فقط إن سجّلت للعب التوقعات): اسم مستخدم، اسم عرض، بريد إلكتروني، رقم هاتف، ورمز الدولة.',
      'Account data (optional — only if you register to play predictions): username, display name, email, phone number, and country code.')) ?></li>
    <li><?= e($L(
      'نشاطك في الألعاب: توقعاتك للمباريات وإجاباتك على سؤال اليوم (لاحتساب النقاط والصدارة).',
      'Game activity: your match predictions and trivia answers (to compute points and the leaderboard).')) ?></li>
    <li><?= e($L(
      'رسائل نموذج التواصل: الاسم والبريد ونص الرسالة، لنرد عليك فقط.',
      'Contact form messages: name, email and the message text, used solely to reply to you.')) ?></li>
    <li><?= e($L(
      'بيانات تقنية: عنوان IP يُستخدم مؤقتاً للحماية من إساءة الاستخدام (حدود المعدّل) وعدّاد زوار مجهول الهوية.',
      'Technical data: IP address used temporarily for abuse protection (rate limiting) and an anonymous visitor counter.')) ?></li>
  </ul>

  <h2><?= e($L('٢. الكوكيز والتخزين المحلي', '2. Cookies & local storage')) ?></h2>
  <ul>
    <li><?= e($L(
      'كوكي الجلسة: يُنشأ فقط عند تسجيل الدخول لإبقائك متصلاً — الزوار بلا حساب لا يحصلون على كوكي جلسة.',
      'Session cookie: created only when you sign in to keep you logged in — visitors without an account receive no session cookie.')) ?></li>
    <li><?= e($L(
      'كوكي اللغة: يحفظ لغتك المفضلة (عربي/إنجليزي/فرنسي).',
      'Language cookie: remembers your preferred language (Arabic/English/French).')) ?></li>
    <li><?= e($L(
      'كوكي حماية النماذج (CSRF): يمنع إرسال النماذج المزوّر.',
      'Form-protection (CSRF) cookie: prevents forged form submissions.')) ?></li>
    <li><?= e($L(
      'التخزين المحلي في متصفحك: تفضيلات تذكير المباريات وتثبيت التطبيق — تبقى على جهازك ولا تُرسل إلينا.',
      'Browser local storage: match-reminder and app-install preferences — kept on your device, never sent to us.')) ?></li>
  </ul>

  <h2><?= e($L('٣. الإعلانات (Google AdSense)', '3. Advertising (Google AdSense)')) ?></h2>
  <p><?= e($L(
    'نعرض إعلانات عبر Google AdSense. تستخدم جوجل وشركاؤها (كطرف ثالث) كوكيز لعرض إعلانات تناسبك بناءً على زياراتك السابقة لهذا الموقع ومواقع أخرى.',
    'We display ads via Google AdSense. Google and its partners (as third parties) use cookies to serve ads relevant to you based on your prior visits to this and other websites.')) ?></p>
  <ul>
    <li><?= $ar
      ? 'يمكنك إيقاف الإعلانات المخصّصة من <a href="https://www.google.com/settings/ads" target="_blank" rel="noopener">إعدادات إعلانات جوجل</a>.'
      : 'You can opt out of personalised advertising in <a href="https://www.google.com/settings/ads" target="_blank" rel="noopener">Google Ads Settings</a>.' ?></li>
    <li><?= $ar
      ? 'أو عبر <a href="https://www.aboutads.info" target="_blank" rel="noopener">aboutads.info</a> لإلغاء كوكيز شبكات إعلانية أخرى.'
      : 'Or via <a href="https://www.aboutads.info" target="_blank" rel="noopener">aboutads.info</a> to opt out of other ad networks’ cookies.' ?></li>
  </ul>

  <h2><?= e($L('٤. خدمات الطرف الثالث', '4. Third-party services')) ?></h2>
  <p><?= e($L(
    'تُحمَّل بعض الموارد من خدمات خارجية: خطوط جوجل (Google Fonts)، صور الأعلام (flagcdn.com)، وبيانات المباريات من مصادر عامة مفتوحة. قد تسجّل هذه الخدمات عنوان IP الخاص بك وفق سياساتها.',
    'Some resources load from external services: Google Fonts, flag images (flagcdn.com), and match data from open public sources. These services may log your IP address per their own policies.')) ?></p>

  <h2><?= e($L('٥. النشرة البريدية', '5. Email digest')) ?></h2>
  <p><?= e($L(
    'إن سجّلت حساباً فقد تصلك نشرة دورية بمستجدات البطولة ونقاطك. كل رسالة تتضمن رابط إلغاء اشتراك يعمل بنقرة واحدة، ولن نراسلك بعدها.',
    'If you register an account you may receive a periodic digest with tournament updates and your points. Every email includes a one-click unsubscribe link, after which we will not email you again.')) ?></p>

  <h2><?= e($L('٦. حماية بياناتك', '6. How we protect your data')) ?></h2>
  <ul>
    <li><?= e($L('كلمات السر تُخزَّن مشفّرة بخوارزمية bcrypt ولا يمكن استرجاعها كنص.',
                 'Passwords are stored hashed with bcrypt and can never be read back as text.')) ?></li>
    <li><?= e($L('الموقع يعمل بالكامل عبر HTTPS المشفّر.',
                 'The entire site is served over encrypted HTTPS.')) ?></li>
    <li><?= e($L('لا نبيع بياناتك ولا نشاركها مع أي جهة لأغراض تسويقية.',
                 'We never sell your data nor share it with anyone for marketing purposes.')) ?></li>
  </ul>

  <h2><?= e($L('٧. حقوقك', '7. Your rights')) ?></h2>
  <p><?= $ar
    ? 'يحق لك طلب الاطلاع على بياناتك أو تصحيحها أو حذف حسابك نهائياً. راسلنا على <a href="mailto:' . e(CONTACT_EMAIL) . '">' . e(CONTACT_EMAIL) . '</a> وسنستجيب خلال أيام عمل معدودة.'
    : 'You may request access to your data, correct it, or permanently delete your account. Email us at <a href="mailto:' . e(CONTACT_EMAIL) . '">' . e(CONTACT_EMAIL) . '</a> and we will respond within a few business days.' ?></p>

  <h2><?= e($L('٨. تحديثات هذه السياسة', '8. Changes to this policy')) ?></h2>
  <p><?= e($L(
    'قد نحدّث هذه الصفحة عند إضافة ميزات جديدة، ويُذكر تاريخ آخر تحديث أعلاها دائماً.',
    'We may update this page when new features are added; the “last updated” date above always reflects the latest revision.')) ?></p>

</div>

<?php tpl('footer'); ?>
