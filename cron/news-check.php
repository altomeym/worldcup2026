<?php
/**
 * cron/news-check.php — تشخيص جلب الأخبار على الخادم + تحديث قسري.
 * /cron/news-check.php?token=INSTALL_TOKEN            ← فحص المصادر
 * /cron/news-check.php?token=...&refresh=1            ← امسح الكاش واجلب من جديد
 */
require __DIR__ . '/../includes/bootstrap.php';
while (ob_get_level() > 0) { ob_end_clean(); }
$tok = (string)($_GET['token'] ?? '');
if (PHP_SAPI !== 'cli' && (!defined('INSTALL_TOKEN') || !hash_equals(INSTALL_TOKEN, $tok))) {
    http_response_code(403); exit('forbidden');
}
header('Content-Type: text/plain; charset=utf-8');

echo "═══ News Diagnostic · " . date('Y-m-d H:i:s') . " ═══\n\n";

// حالة الكاش
foreach (['ar', 'en', 'fr'] as $lg) {
    $f = rtrim(CACHE_DIR, '/') . "/news_{$lg}.json";
    echo "[{$lg}] cache: " . (is_file($f)
        ? round((time() - filemtime($f)) / 3600, 1) . "h · " . round(filesize($f) / 1024) . "KB"
        : 'غير موجود');
    echo is_file($f . '.fail') ? "  ⚠️ fail-marker (" . round((time() - filemtime($f . '.fail')) / 60) . "m)" : '';
    echo "\n";
}

// فحص المصادر مباشرة من هذا الخادم
echo "\n[المصادر]\n";
$feeds = [
    'Bing AR'   => defined('NEWS_RSS_AR')  ? NEWS_RSS_AR  : '',
    'Google AR' => defined('NEWS_RSS_AR2') ? NEWS_RSS_AR2 : '',
    'Bing EN'   => defined('NEWS_RSS_EN')  ? NEWS_RSS_EN  : '',
];
$ua = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0 Safari/537.36';
foreach ($feeds as $name => $u) {
    if ($u === '') continue;
    $raw = http_get($u, ['timeout' => 10, 'ua' => $ua]);
    $n = $raw ? substr_count($raw, '<item>') : 0;
    printf("  %-10s → %s · %d عنصر\n", $name, $raw === null ? '✗ فشل الاتصال/محظور' : '✓ ' . strlen($raw) . 'B', $n);
}

// تحديث قسري
if (isset($_GET['refresh'])) {
    echo "\n[تحديث قسري]\n";
    foreach (['ar', 'en'] as $lg) {
        $f = rtrim(CACHE_DIR, '/') . "/news_{$lg}.json";
        @unlink($f); @unlink($f . '.fail');
        $items = News::latest(3, $lg);
        printf("  %s → %d خبر · أحدثها: %s\n", $lg, count($items),
            $items ? date('D H:i', (int)($items[0]['ts'] ?? 0)) : '—');
    }
}
echo "\n✓ انتهى\n";
