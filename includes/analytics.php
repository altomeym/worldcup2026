<?php
/**
 * analytics.php — Google Tag Manager: سياق الصفحة وأحداث dataLayer.
 */
if (!defined('WC2026')) { exit('Access denied'); }

/** هل GTM مفعّل؟ */
function gtm_enabled(): bool {
    return defined('GTM_CONTAINER_ID') && GTM_CONTAINER_ID !== '';
}

/** تصنيف الصفحة الحالية لـ GA4 (Custom dimension في GTM). */
function gtm_page_type(): string {
    $page = preg_replace('/\.php$/', '', basename($_SERVER['SCRIPT_NAME'] ?? 'index.php')) ?: 'index';
    static $map = [
        'index'      => 'home',
        'today'      => 'matches',
        'matches'    => 'matches',
        'match'      => 'match',
        'news'       => 'news',
        'article'    => 'article',
        'predict'    => 'predict',
        'bracket'    => 'predict',
        'leaderboard'=> 'predict',
        'leagues'    => 'predict',
        'league'     => 'predict',
        'stickers'   => 'predict',
        'trivia'     => 'predict',
        'promote'    => 'predict',
        'groups'     => 'tournament',
        'knockout'   => 'tournament',
        'teams'      => 'tournament',
        'team'       => 'tournament',
        'squads'     => 'tournament',
        'stadiums'   => 'tournament',
        'stadium'    => 'tournament',
        'map'        => 'tournament',
        'fanguide'   => 'tournament',
        'archive'    => 'tournament',
        'stats'      => 'stats',
        'dashboard'  => 'stats',
        'physical'   => 'stats',
        'motm'       => 'stats',
        'topscorers' => 'stats',
        'bookings'   => 'stats',
        'referees'   => 'stats',
        'referee'    => 'stats',
        'player'     => 'stats',
        'card'       => 'share_card',
        'about'      => 'info',
        'terms'      => 'info',
        'privacy'    => 'info',
        'install-app'=> 'pwa',
        'embed'      => 'embed',
        'login'      => 'account',
        'register'   => 'account',
    ];
    return $map[$page] ?? $page;
}

/** قسم الموقع (لتجميع التقارير في GTM). */
function gtm_site_section(): string {
    $type = gtm_page_type();
    static $sections = [
        'home'       => 'core',
        'matches'    => 'core',
        'match'      => 'core',
        'news'       => 'content',
        'article'    => 'content',
        'predict'    => 'engagement',
        'share_card' => 'engagement',
        'tournament' => 'tournament',
        'stats'      => 'stats',
        'info'       => 'trust',
        'pwa'        => 'app',
        'embed'      => 'distribution',
        'account'    => 'account',
    ];
    return $sections[$type] ?? 'other';
}

/** بيانات dataLayer الأولية — تُحقَن قبل تحميل GTM. */
function gtm_sanitize_value(mixed $v): mixed {
    if ($v === null || $v === false) {
        return null;
    }
    if (is_bool($v)) {
        return $v ? 1 : 0;
    }
    if (is_int($v) || is_float($v)) {
        return $v;
    }
    if (is_string($v)) {
        $v = trim($v);
        return $v === '' ? null : mb_substr($v, 0, 120, 'UTF-8');
    }
    return null;
}

/** يضيف حقولاً لسياق الصفحة (يُستدعى قبل tpl('header')). */
function gtm_add(array $data): void {
    if (!gtm_enabled()) {
        return;
    }
    $clean = [];
    foreach ($data as $key => $value) {
        if (!is_string($key) || $key === '' || $key === 'event') {
            continue;
        }
        $v = gtm_sanitize_value($value);
        if ($v !== null) {
            $clean[$key] = $v;
        }
    }
    if ($clean === []) {
        return;
    }
    $GLOBALS['_gtm_extra'] = array_merge($GLOBALS['_gtm_extra'] ?? [], $clean);
}

/** بيانات dataLayer الأولية — تُحقَن قبل تحميل GTM. */
function gtm_page_context(): array {
    $ctx = [
        'event'         => 'page_context',
        'page_type'     => gtm_page_type(),
        'site_section'  => gtm_site_section(),
        'page_lang'     => current_lang(),
        'page_path'     => basename($_SERVER['SCRIPT_NAME'] ?? 'index.php'),
    ];
    if (defined('GA4_MEASUREMENT_ID') && GA4_MEASUREMENT_ID !== '') {
        $ctx['ga4_id'] = GA4_MEASUREMENT_ID;
    }
    $extra = $GLOBALS['_gtm_extra'] ?? [];
    if ($extra !== []) {
        $ctx = array_merge($ctx, $extra);
    }
    return $ctx;
}
