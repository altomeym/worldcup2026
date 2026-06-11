<?php
/**
 * cron/api-check.php — تشخيص شامل لربط API-Football.
 * ============================================================
 * يفحص:
 *   ١) المفتاح + الاتصال
 *   ٢) خريطة المباريات (fixtures map)
 *   ٣) النتائج اللحظية (live scores)
 *   ٤) الإحصائيات لمباراة معيّنة (statsFor)
 *   ٥) التشكيلة (lineupForMatch)
 *   ٦) الحصّة المتبقّية اليوميّة
 *
 * الاستخدام:
 *   /cron/api-check.php?token=INSTALL_TOKEN
 *   /cron/api-check.php?token=...&match=0  ← اختبر مباراة معيّنة
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

$matchIdx = (int)($_GET['match'] ?? 0);
$clear    = isset($_GET['clear']);

echo str_repeat('═', 60) . "\n";
echo "  🔌 API-Football Connection Diagnostic  ·  " . date('Y-m-d H:i:s') . "\n";
echo str_repeat('═', 60) . "\n\n";

// 🆕 تنظيف الكاش بالكامل
if ($clear) {
    $cleared = [];
    foreach (glob(rtrim(CACHE_DIR,'/').'/af-*.json') ?: [] as $f) { @unlink($f); $cleared[] = basename($f); }
    foreach (glob(rtrim(CACHE_DIR,'/').'/live*.json') ?: [] as $f) { @unlink($f); $cleared[] = basename($f); }
    foreach (glob(rtrim(CACHE_DIR,'/').'/live*.json.fail') ?: [] as $f) { @unlink($f); $cleared[] = basename($f); }
    echo "[CLEAR] تم حذف " . count($cleared) . " ملفّ كاش:\n";
    foreach ($cleared as $f) echo "   ✓ {$f}\n";
    echo "\nالآن طلب جديد سيُجلب من API-Football مباشرة.\n";
    echo "أعد تحميل الصفحة بدون &clear=1 للمتابعة.\n";
    exit;
}

// ────────────────────────────────────────────────
// (1) المفتاح والإعداد
// ────────────────────────────────────────────────
echo "[1] الإعداد\n";
$haveKey = defined('APIFOOTBALL_KEY') && APIFOOTBALL_KEY !== '';
echo "    " . ($haveKey ? '✓' : '✗') . " APIFOOTBALL_KEY: " . ($haveKey ? '[set, ' . strlen(APIFOOTBALL_KEY) . ' chars]' : 'MISSING') . "\n";
echo "    " . (defined('APIFOOTBALL_HOST') ? '✓' : '✗') . " APIFOOTBALL_HOST: " . (defined('APIFOOTBALL_HOST') ? APIFOOTBALL_HOST : 'MISSING') . "\n";
echo "    " . (defined('APIFOOTBALL_LEAGUE') ? '✓' : '✗') . " APIFOOTBALL_LEAGUE: " . (defined('APIFOOTBALL_LEAGUE') ? APIFOOTBALL_LEAGUE : 'MISSING') . "\n";
echo "    " . (defined('APIFOOTBALL_SEASON') ? '✓' : '✗') . " APIFOOTBALL_SEASON: " . (defined('APIFOOTBALL_SEASON') ? APIFOOTBALL_SEASON : 'MISSING') . "\n";
echo "    LiveService::isEnabled(): " . (LiveService::isEnabled() ? '✓ مفعّل' : '✗ معطّل') . "\n\n";

if (!$haveKey) {
    echo "⛔ لا يمكن المتابعة بدون مفتاح API-Football.\n";
    echo "   أضِفه في config.local.php: define('APIFOOTBALL_KEY', '...');\n";
    exit;
}

// ────────────────────────────────────────────────
// (2) اختبار اتصال — استدعاء /status
// ────────────────────────────────────────────────
echo "[2] اختبار اتصال — /status\n";
$url = 'https://' . APIFOOTBALL_HOST . '/status';
$ctx = stream_context_create(['http' => [
    'method' => 'GET',
    'header' => "x-apisports-key: " . APIFOOTBALL_KEY . "\r\n",
    'timeout' => 15,
]]);
$raw = @file_get_contents($url, false, $ctx);
if ($raw === false) {
    echo "    ✗ فشل الاتصال — تحقّق من المفتاح/الإنترنت\n\n";
} else {
    $st = json_decode($raw, true);
    $req     = (int)($st['response']['requests']['current'] ?? 0);
    $limit   = (int)($st['response']['requests']['limit_day'] ?? 100);
    $remain  = $limit - $req;
    echo "    ✓ متّصل بنجاح\n";
    echo "    📊 الحصّة اليوميّة: {$req} / {$limit}  (متبقّ: {$remain})\n";
    if ($remain < 10) echo "    ⚠️ تحذير: حصّة منخفضة جداً!\n";
    echo "\n";
}

// ────────────────────────────────────────────────
// (3) خريطة المباريات
// ────────────────────────────────────────────────
echo "[3] خريطة المباريات (fixturesMap)\n";
$ref = new ReflectionClass('LiveService');
$fxMethod = $ref->getMethod('fixturesMap');
$fxMethod->setAccessible(true);
$map = $fxMethod->invoke(null);
echo "    ✓ تمّ التحميل: " . count($map) . " مباراة\n";
// طبع أوّل 3 لمعرفة شكل المفاتيح والـIDs
foreach (array_slice($map, 0, 3, true) as $k => $v) {
    echo "      • {$k} → id:" . ($v['id'] ?? '?') . " ref:" . ($v['referee'] ?? '—') . "\n";
}

// ────────────────────────────────────────────────
// (4) النتائج اللحظيّة (public)
// ────────────────────────────────────────────────
echo "\n[4] النتائج اللحظيّة (liveScores)\n";
$live = LiveService::liveScores();
echo "    " . (is_array($live) ? '✓' : '✗') . " liveScores: " . (is_array($live) ? count($live) . " مباراة" : 'فشل') . "\n";
if (is_array($live) && $live) {
    foreach (array_slice($live, 0, 3, true) as $key => $hit) {
        $ft = is_array($hit['ft'] ?? null) ? "{$hit['ft'][0]}-{$hit['ft'][1]}" : '?';
        echo "      • {$key} → status:" . ($hit['status'] ?? '?') . " score:{$ft}\n";
    }
}

// ────────────────────────────────────────────────
// (5) المباراة المختارة (الافتتاح افتراضياً)
// ────────────────────────────────────────────────
$m = DataService::matchByIndex($matchIdx);
if (!$m) { echo "\n⛔ مباراة #{$matchIdx} غير موجودة\n"; exit; }
$t1 = $m['team1'] ?? '?'; $t2 = $m['team2'] ?? '?';
echo "\n[5] المباراة #{$matchIdx}: {$t1} vs {$t2}\n";
echo "    التاريخ: " . ($m['date'] ?? '?') . " " . ($m['time'] ?? '?') . "\n";

// قبل applyTo (خام)
echo "\n    حالة قبل applyTo:\n";
echo "      _status:   " . ($m['_status'] ?? '?') . "\n";
echo "      _live:     " . (!empty($m['_live']) ? 'true' : 'false') . "\n";
echo "      score:     " . (isset($m['score']['ft']) ? implode('-', $m['score']['ft']) : 'لا يوجد') . "\n";

// applyTo
$m = LiveService::applyTo($m);
echo "\n    حالة بعد applyTo:\n";
echo "      _live:        " . (!empty($m['_live']) ? 'true ⚡' : 'false') . "\n";
echo "      _live_minute: " . (isset($m['_live_minute']) ? $m['_live_minute'] : 'null') . "\n";
echo "      score:        " . (isset($m['score']['ft']) ? implode('-', $m['score']['ft']) : 'لا يوجد') . "\n";
echo "      referee:      " . ($m['referee'] ?? 'لا يوجد') . "\n";
echo "      stats count:  " . (is_array($m['stats'] ?? null) ? count($m['stats']) : '0') . "\n";
echo "      cards count:  " . (is_array($m['cards'] ?? null) ? count($m['cards']) : '0') . "\n";

// ────────────────────────────────────────────────
// (6) اختبار statsFor مباشرة
// ────────────────────────────────────────────────
echo "\n[6] statsFor مباشرة\n";
$stats = LiveService::statsFor($m);
echo "    " . ($stats ? '✓' : '✗') . " " . count($stats) . " إحصائيّة\n";
if ($stats) {
    foreach (array_slice($stats, 0, 6) as $s) {
        printf("      %s: %d - %d %s\n", $s['k'] ?? '?', $s['v'][0] ?? 0, $s['v'][1] ?? 0, $s['unit'] ?? '');
    }
}

// ────────────────────────────────────────────────
// (7) اختبار التشكيلة
// ────────────────────────────────────────────────
echo "\n[7] lineupForMatch\n";
if (method_exists('LiveService', 'lineupForMatch')) {
    $lu = LiveService::lineupForMatch($m);
    echo "    " . ($lu ? '✓' : '✗') . " " . ($lu ? 'تشكيلة حقيقيّة' : 'لا توجد — ستظهر متوقّعة') . "\n";
    if (is_array($lu)) {
        foreach (['team1', 'team2'] as $side) {
            if (!empty($lu[$side])) {
                echo "      [{$side}] formation: " . ($lu[$side]['formation'] ?? '?')
                   . " · " . count($lu[$side]['start'] ?? []) . " لاعب أساسي\n";
            }
        }
    }
} else {
    echo "    ⚠️ lineupForMatch غير معرّفة\n";
}

// ────────────────────────────────────────────────
// (8) استدعاء /fixtures/statistics مباشرة (للتشخيص)
// ────────────────────────────────────────────────
echo "\n[8] استدعاء /fixtures/statistics مباشرة\n";
$hit = $map[LiveService::normalizeKey($t1, $t2) ?? ''] ?? ($map[LiveService::normalizeKey($t2, $t1) ?? ''] ?? null);
if (!is_array($hit) || empty($hit['id'])) {
    // ربما normalizeKey خاص — جرّب باسم الفريق المضيف
    foreach ($map as $k => $h) {
        if (stripos($k, strtolower(substr($t1, 0, 5))) !== false) { $hit = $h; break; }
    }
}
// لو مش لقينا، جرّب البحث بالـsubstring
if (!is_array($hit) || empty($hit['id'])) {
    foreach ($map as $k => $h) {
        if (stripos($k, strtolower($t1)) !== false || stripos($k, strtolower(str_replace(' ', '-', $t1))) !== false) {
            $hit = $h; break;
        }
    }
}
if (is_array($hit) && !empty($hit['id'])) {
    $fid = (int)$hit['id'];
    echo "    Fixture ID: {$fid}\n";
    $url = 'https://' . APIFOOTBALL_HOST . '/fixtures/statistics?fixture=' . $fid;
    $ctx = stream_context_create(['http' => [
        'method' => 'GET', 'header' => "x-apisports-key: " . APIFOOTBALL_KEY . "\r\n", 'timeout' => 15,
    ]]);
    $raw = @file_get_contents($url, false, $ctx);
    if ($raw === false) {
        echo "    ✗ طلب فشل\n";
    } else {
        $j = json_decode($raw, true);
        $n = count($j['response'] ?? []);
        echo "    ✓ ردّ API: {$n} فريق برَدّ statistics\n";
        if ($n === 0) echo "    💡 المباراة لم تبدأ بعد أو API-Football لم يحدّث البيانات بعد.\n";
    }
} else {
    echo "    ✗ لم نجد fixture_id لـ{$t1} vs {$t2} في الخريطة\n";
}

echo "\n" . str_repeat('═', 60) . "\n";
echo "  ✓ انتهى التشخيص\n";
echo str_repeat('═', 60) . "\n";
