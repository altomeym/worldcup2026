<?php
/**
 * terms.php — شروط الاستخدام (متطلب AdSense — إخلاء مسؤولية بث).
 */
require __DIR__ . '/includes/bootstrap.php';

$ar = (current_lang() === 'ar');
$L  = fn(string $a, string $e) => $ar ? $a : $e;
$host = parse_url(SITE_URL, PHP_URL_HOST) ?: 'foot-boll.com';

$page_title = t('terms_title');
$page_desc  = $L(
    'شروط استخدام foot-boll.com — نتائج وإحصائيات وتوقعات، بدون بث مباشر.',
    'Terms of use for foot-boll.com — scores, stats, and predictions, no live streaming.'
);

tpl('header');
?>

<div class="page-head">
  <h1><?= e($page_title) ?></h1>
  <p class="muted"><?= e($L('آخر تحديث: يوليو 2026', 'Last updated: July 2026')) ?></p>
</div>

<div class="prose" style="max-width:780px;margin:0 auto;line-height:2">

  <p><?= e($L(
    'باستخدامك ' . $host . ' فإنك توافق على هذه الشروط. إن لم توافق، يرجى عدم استخدام الموقع.',
    'By using ' . $host . ' you agree to these terms. If you do not agree, please do not use the site.'
  )) ?></p>

  <h2><?= e($L('١. طبيعة الخدمة', '1. Nature of the service')) ?></h2>
  <p><?= e($L(
    'الموقع يعرض معلومات رياضية: جداول، نتائج، إحصائيات، ومسابقات توقع. لا نبث المباريات ولا نروّج لخدمات بث غير رسمية. أي روابط خارجية للأخبار تؤدي إلى مواقع الناشرين الأصليين.',
    'The site displays sports information: schedules, results, statistics, and prediction games. We do not stream matches or promote unofficial streaming. External news links lead to original publishers.'
  )) ?></p>

  <h2><?= e($L('٢. مسابقات التوقعات', '2. Prediction games')) ?></h2>
  <p><?= e($L(
    'التوقعات مجانية للترفيه. النقاط والصدارة ليست أموالاً حقيقية ولا قابلة للصرف. الموقع ليس موقع مراهنات.',
    'Predictions are free for entertainment. Points and rankings are not real money and cannot be redeemed. This is not a gambling site.'
  )) ?></p>

  <h2><?= e($L('٣. المحتوى والحقوق', '3. Content and rights')) ?></h2>
  <p><?= e($L(
    'بيانات الجدول من openfootball (ملك عام). شعارات المنتخبات والصور من مصادر طرف ثالث. مقالات RSS: نعرض العنوان والملخّص فقط — المحتوى الكامل على موقع الناشر.',
    'Schedule data from openfootball (public domain). Team logos and images from third parties. RSS: we show title and snippet only — full content stays on the publisher\'s site.'
  )) ?></p>

  <h2><?= e($L('٤. الحسابات', '4. Accounts')) ?></h2>
  <p><?= e($L(
    'إنشاء حساب اختياري للعب التوقعات. أنت مسؤول عن سرّ كلمة المرور. قد نعلّق حساباً يسيء استخدام الخدمة.',
    'Account creation is optional for prediction games. You are responsible for your password. We may suspend accounts that abuse the service.'
  )) ?></p>

  <h2><?= e($L('٥. إخلاء مسؤولية', '5. Disclaimer')) ?></h2>
  <p><?= e($L(
    'نبذل جهدنا لدقة البيانات لكن لا نضمن خلوّها من الأخطاء. الموقع «كما هو» دون ضمانات. لقرارات مهمة راجع المصادر الرسمية (FIFA).',
    'We strive for accuracy but do not guarantee error-free data. The site is provided "as is" without warranties. For important decisions, consult official sources (FIFA).'
  )) ?></p>

  <h2><?= e($L('٦. الإعلانات', '6. Advertising')) ?></h2>
  <p><?= e($L(
    'قد نعرض إعلانات Google AdSense. راجع ',
    'We may display Google AdSense ads. See '
  )) ?><a href="<?= e(url('privacy.php')) ?>"><?= e($L('سياسة الخصوصية', 'Privacy Policy')) ?></a><?= e($L(' للتفاصيل.', ' for details.')) ?></p>

  <h2><?= e($L('٧. التعديلات', '7. Changes')) ?></h2>
  <p><?= e($L(
    'قد نحدّث هذه الشروط. يُذكر تاريخ التحديث أعلاه.',
    'We may update these terms. The date above reflects the latest revision.'
  )) ?></p>

</div>

<?php tpl('footer'); ?>
