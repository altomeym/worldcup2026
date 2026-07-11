<?php if (!defined('WC2026') || !gtm_enabled()) { return; } ?>
<?php require __DIR__ . '/gtm-data.php'; ?>
<?php
$gtmJsV = @filemtime(__DIR__ . '/../assets/js/analytics.js') ?: 1;
$gtmId  = GTM_CONTAINER_ID;
?>
<script src="<?= e(rtrim(SITE_URL, '/') ?: '') ?>/assets/js/analytics.js?v=<?= (int)$gtmJsV ?>"></script>
<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','<?= e($gtmId) ?>');</script>
<!-- End Google Tag Manager -->
