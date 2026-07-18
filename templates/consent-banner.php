<?php
/**
 * شريط موافقة الكوكيز — يظهر مرة واحدة حتى يقبل الزائر.
 */
if (!defined('WC2026') || !function_exists('consent_banner_needed') || !consent_banner_needed()) {
    return;
}
$ar = (current_lang() === 'ar');
$L  = fn(string $a, string $e) => $ar ? $a : $e;
?>
<aside class="fb-consent" id="fbConsent" role="dialog" aria-live="polite" aria-label="<?= e($L('موافقة الكوكيز', 'Cookie consent')) ?>">
  <div class="fb-consent-in wrap">
    <p class="fb-consent-text">
      <?= e($L(
          'نستخدم كوكيز ضرورية لتشغيل الموقع، وكوكيز اختيارية للتحليلات وإعلانات Google AdSense. بقبولك توافق على الكوكيز غير الضرورية وفق ',
          'We use essential cookies to run the site, and optional cookies for analytics and Google AdSense ads. By accepting, you agree to non-essential cookies as described in our '
      )) ?>
      <a href="<?= e(url('privacy.php')) ?>"><?= e($L('سياسة الخصوصية', 'Privacy Policy')) ?></a><?= e($L('.', '.')) ?>
    </p>
    <div class="fb-consent-actions">
      <button type="button" class="btn btn-accent fb-consent-accept" id="fbConsentAccept"><?= e($L('موافق', 'Accept')) ?></button>
      <a class="btn fb-consent-more" href="<?= e(url('privacy.php')) ?>"><?= e($L('التفاصيل', 'Details')) ?></a>
    </div>
  </div>
</aside>
<script>
(function () {
  var box = document.getElementById('fbConsent');
  var btn = document.getElementById('fbConsentAccept');
  if (!box || !btn) return;
  btn.addEventListener('click', function () {
    var maxAge = 365 * 24 * 60 * 60;
    var secure = location.protocol === 'https:' ? '; Secure' : '';
    document.cookie = 'wc_consent=1; Max-Age=' + maxAge + '; Path=/; SameSite=Lax' + secure;
    box.hidden = true;
    // إعادة تحميل خفيفة لتفعيل GTM بعد الموافقة
    location.reload();
  });
})();
</script>
