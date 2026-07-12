<?php
/**
 * cron/fifa-feed.php — تحديث fifa-metrics + photos + motm من fifaphy (استضافة مشتركة).
 *
 * بديل PHP-only لـ tools/fifa-feed-cron.sh — أنسب لـ Namecheap (لا bash/curl/CRLF).
 *
 * Cron (كل ساعة أثناء البطولة):
 *   /usr/local/bin/php /home/USER/public_html/cron/fifa-feed.php
 *
 * أو متصفّح (محمي):
 *   /cron/fifa-feed.php?token=INSTALL_TOKEN
 */
require __DIR__ . '/../includes/bootstrap.php';
while (ob_get_level() > 0) { ob_end_clean(); }

$cli = (PHP_SAPI === 'cli');
if (!$cli) {
    $tok = (string)($_GET['token'] ?? '');
    if (!defined('INSTALL_TOKEN') || INSTALL_TOKEN === '' || !hash_equals(INSTALL_TOKEN, $tok)) {
        http_response_code(403);
        exit('forbidden');
    }
    header('Content-Type: text/plain; charset=utf-8');
}

$feedDir = __DIR__ . '/../tools/_feed';
if (!is_dir($feedDir) && !@mkdir($feedDir, 0755, true)) {
    echo "error: cannot create _feed dir\n";
    exit(1);
}

$urls = [
    'data.js'    => 'https://fifaphy.vercel.app/data.js',
    'ratings.js' => 'https://fifaphy.vercel.app/ratings.js',
    'posreal.js' => 'https://fifaphy.vercel.app/posreal.js',
];

foreach ($urls as $name => $url) {
    $raw = http_get($url, ['timeout' => 90, 'ua' => 'wcup2026-fifa-feed/1.0', 'redirects' => true]);
    if ($raw === null || $raw === '') {
        echo "{$name} — download failed\n";
        exit(1);
    }
    $tmp = $feedDir . '/' . $name . '.tmp';
    if (@file_put_contents($tmp, $raw) === false || !@rename($tmp, $feedDir . '/' . $name)) {
        echo "{$name} — write failed\n";
        exit(1);
    }
    echo "{$name} — ok (" . strlen($raw) . " bytes)\n";
}

$argv = ['fifa-metrics-build.php', $feedDir];
$argc = 2;
include __DIR__ . '/../tools/fifa-metrics-build.php';

if (function_exists('cron_heartbeat')) {
    cron_heartbeat('fifa-feed', 'ok');
}

echo "done — " . date('Y-m-d H:i:s') . "\n";
