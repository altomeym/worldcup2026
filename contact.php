<?php
/**
 * contact.php — صفحة تواصل قابلة للفهرسة (مطلوبة لمراجعة AdSense).
 */
require __DIR__ . '/includes/bootstrap.php';

$ar   = (current_lang() === 'ar');
$L    = fn(string $a, string $e) => $ar ? $a : $e;
$host = parse_url(SITE_URL, PHP_URL_HOST) ?: 'foot-boll.com';

$page_title = $L('تواصل معنا', 'Contact us');
$page_desc  = $L(
    "تواصل مع فريق {$host}: استفسارات، رعاية، وشراكات حول كأس العالم 2026.",
    "Contact the {$host} team: inquiries, sponsorship, and partnerships for World Cup 2026."
);
$page_keywords = $L(
    'تواصل, رعاية, foot-boll, كأس العالم 2026',
    'contact, sponsorship, foot-boll, World Cup 2026'
);

tpl('header');
?>

<div class="page-head">
  <h1><?= e($page_title) ?></h1>
  <p class="muted"><?= e($L(
      'نرحّب باستفساراتك واقتراحاتك وفرص الرعاية. نرد عادة خلال أيام عمل معدودة.',
      'We welcome questions, suggestions, and sponsorship opportunities. We usually reply within a few business days.'
  )) ?></p>
</div>

<div class="prose" style="max-width:640px;margin:0 auto;line-height:2">
  <p><?= $ar
    ? 'البريد المباشر: <a href="mailto:' . e(CONTACT_EMAIL) . '">' . e(CONTACT_EMAIL) . '</a>'
    : 'Direct email: <a href="mailto:' . e(CONTACT_EMAIL) . '">' . e(CONTACT_EMAIL) . '</a>' ?></p>

  <p><?= e($L(
      'أو أرسل رسالتك عبر النموذج أدناه:',
      'Or send your message using the form below:'
  )) ?></p>

  <p style="text-align:center;margin:28px 0">
    <button type="button" class="btn btn-accent" data-contact-open><?= e(t('contact_title')) ?></button>
  </p>

  <h2><?= e($L('ما يمكن أن نساعدك فيه', 'What we can help with')) ?></h2>
  <ul>
    <li><?= e($L('أسئلة عن المحتوى والتحليلات والبيانات على الموقع', 'Questions about site content, analytics, and data')) ?></li>
    <li><?= e($L('طلبات الرعاية والشراكات الإعلامية', 'Sponsorship and media partnership requests')) ?></li>
    <li><?= e($L('مشاكل تقنية أو اقتراحات لتحسين التجربة', 'Technical issues or suggestions to improve the experience')) ?></li>
    <li><?= e($L('طلبات الخصوصية وحذف الحساب', 'Privacy requests and account deletion')) ?></li>
  </ul>

  <p class="muted"><?= e($host) ?> · <?= e($L('أُعدّ بعناية لعشّاق كرة القدم العرب', 'Crafted for Arabic football fans')) ?></p>
</div>

<?php tpl('footer'); ?>
