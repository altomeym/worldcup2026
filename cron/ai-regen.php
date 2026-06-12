<?php
/**
 * cron/ai-regen.php — مسح تقرير AI لمباراة وإعادة توليده فوراً.
 * ============================================================
 * الاستخدام:
 *   /cron/ai-regen.php?token=INSTALL_TOKEN&match=0          ← امسح وولّد من جديد
 *   /cron/ai-regen.php?token=INSTALL_TOKEN&match=0&purge=1  ← امسح فقط (يتولّد عند أوّل زيارة)
 *
 * متى تحتاجه؟ عند ظهور تقرير بأرقام خاطئة (نموذج قديم/نتيجة مبكّرة) —
 * المسح يجبر التوليد الجديد بالنموذج الأدقّ + فحص الأرقام.
 * ============================================================
 */
require __DIR__ . '/../includes/bootstrap.php';
while (ob_get_level() > 0) { ob_end_clean(); }

$cli = (PHP_SAPI === 'cli');
if (!$cli) {
    $tok = (string)($_GET['token'] ?? '');
    if (!defined('INSTALL_TOKEN') || INSTALL_TOKEN === '' || !hash_equals(INSTALL_TOKEN, $tok)) {
        http_response_code(403); exit('forbidden');
    }
    header('Content-Type: text/plain; charset=utf-8');
}

// وسائط CLI بصيغة match=0 purge=1
if ($cli) {
    foreach (array_slice($argv ?? [], 1) as $a) {
        if (preg_match('/^([^=]+)=(.*)$/', $a, $mm)) $_GET[$mm[1]] = $mm[2];
    }
}
$idx   = (int)($_GET['match'] ?? -1);
$purge = isset($_GET['purge']);
if ($idx < 0) { echo "استخدم: ?match=N (رقم المباراة، 0 للافتتاح)\n"; exit; }

echo "═══ AI Report Regen — مباراة #{$idx} ═══\n\n";

// (1) امسح كل نسخ التقرير المخزّنة لهذه المباراة (كل اللغات/النتائج)
$deleted = 0;
foreach (glob(rtrim(CACHE_DIR, '/') . "/ai_summary_{$idx}_*.txt") ?: [] as $f) {
    @unlink($f); $deleted++;
    echo "  🗑 " . basename($f) . "\n";
}
foreach (glob(rtrim(CACHE_DIR, '/') . "/ai_summary_{$idx}_*.txt.fail") ?: [] as $f) {
    @unlink($f);
}
echo "  تم مسح {$deleted} ملفّ.\n\n";

if ($purge) { echo "✓ مسح فقط — سيتولّد التقرير الجديد عند أوّل زيارة للصفحة.\n"; exit; }

// (2) أعد التوليد فوراً (بالعربية والإنجليزية)
$m = DataService::matchByIndex($idx);
if (!$m) { echo "✗ مباراة غير موجودة\n"; exit(1); }
$m = LiveService::applyTo($m);

if (!isset($m['score']['ft'])) { echo "○ المباراة لم تنتهِ بعد — لا تقرير.\n"; exit; }
echo "النتيجة: {$m['team1']} {$m['score']['ft'][0]} - {$m['score']['ft'][1]} {$m['team2']}\n\n";

@set_time_limit(120);
foreach (['ar', 'en'] as $lang) {
    echo "── توليد ({$lang})...\n";
    $text = AiContent::forMatch($m, 'summary', $lang);
    if ($text !== null) {
        echo "✓ نجح (" . mb_strlen($text) . " حرفاً):\n";
        echo "  " . mb_substr($text, 0, 220) . "…\n\n";
    } else {
        echo "✗ فشل/رُفض بفحص الأرقام — سيُعاد لاحقاً تلقائياً.\n\n";
    }
}
echo "✓ انتهى.\n";
