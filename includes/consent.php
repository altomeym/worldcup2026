<?php
/**
 * consent.php — موافقة الكوكيز غير الضرورية (تحليلات).
 * AdSense يبقى متاحاً لـ AdsBot/Googlebot وللمراجعة؛ GTM يُحمَّل بعد الموافقة.
 */
if (!defined('WC2026')) { exit('Access denied'); }

const CONSENT_COOKIE = 'wc_consent';

/** هل زاحف إعلانات/بحث من جوجل؟ */
function is_ads_or_googlebot(): bool {
    $ua = (string)($_SERVER['HTTP_USER_AGENT'] ?? '');
    return $ua !== '' && (bool)preg_match(
        '/Mediapartners-Google|AdsBot-Google|Googlebot|Google-InspectionTool/i',
        $ua
    );
}

/** وافق الزائر على الكوكيز غير الضرورية */
function consent_given(): bool {
    return (($_COOKIE[CONSENT_COOKIE] ?? '') === '1') || is_ads_or_googlebot();
}

/** هل نعرض شريط الموافقة؟ */
function consent_banner_needed(): bool {
    if (is_ads_or_googlebot()) {
        return false;
    }
    return ($_COOKIE[CONSENT_COOKIE] ?? '') === '';
}
