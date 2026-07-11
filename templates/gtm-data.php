<?php
if (!defined('WC2026') || !gtm_enabled()) { return; }
$gtmCtx = gtm_page_context();
?>
<script>
window.dataLayer = window.dataLayer || [];
dataLayer.push(<?= json_encode($gtmCtx, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>);
</script>
