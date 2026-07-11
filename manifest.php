<?php
/**
 * manifest.php — بيان تطبيق الويب (PWA) ديناميكي حسب اللغة والنطاق.
 */
require __DIR__ . '/includes/bootstrap.php';

header('Content-Type: application/manifest+json; charset=utf-8');
header('Cache-Control: public, max-age=86400');

$lang = current_lang();
$base = rtrim(SITE_URL, '/');

echo json_encode([
    'name'             => t('site_title_suffix'),
    'short_name'       => $lang === 'ar' ? 'مونديال 26' : 'WC 26',
    'description'      => t('site_desc'),
    'lang'             => $lang,
    'dir'              => $lang === 'ar' ? 'rtl' : 'ltr',
    'start_url'        => $base . '/index.php?lang=' . $lang,
    'scope'            => $base . '/',
    'display'          => 'standalone',
    'orientation'      => 'portrait-primary',
    'background_color' => '#0a1626',
    'theme_color'      => '#0a1626',
    'icons'            => [
        ['src' => $base . '/assets/img/icon-192.png', 'sizes' => '192x192', 'type' => 'image/png'],
        ['src' => $base . '/assets/img/icon-512.png', 'sizes' => '512x512', 'type' => 'image/png'],
        ['src' => $base . '/assets/img/icon-maskable-512.png', 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'maskable'],
    ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
