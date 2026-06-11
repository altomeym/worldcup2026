<?php
/**
 * ping.php — نقطة مراقبة التوافر (UptimeRobot وأمثاله).
 * ============================================================
 * خفيفة عمداً: بلا bootstrap، بلا جلسات، بلا أي اتصال شبكي أو قاعدة بيانات.
 * تتحقق أن PHP يعمل وأن كاش بيانات البطولة موجود وسليم.
 *   سليم   → 200 + {"ok":true}
 *   معطوب → 500 + {"ok":false}  ← المراقب يرسل تنبيهاً فوراً
 * اضبط المراقب على: فحص كل 5 دقائق + كلمة مفتاحية "ok":true
 * ============================================================
 */
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$f       = __DIR__ . '/cache/worldcup2026.json';
$exists  = is_file($f);
$ageSec  = $exists ? (time() - (int)filemtime($f)) : -1;
$matches = 0;

if ($exists) {
    $d = json_decode((string)@file_get_contents($f), true);
    $matches = is_array($d['matches'] ?? null) ? count($d['matches']) : 0;
}

// سليم = الكاش موجود وفيه مباريات (PHP + القرص + خط البيانات كلها تعمل)
$healthy = $exists && $matches > 0;

http_response_code($healthy ? 200 : 500);
echo json_encode([
    'ok'               => $healthy,
    'matches'          => $matches,
    'data_age_seconds' => $ageSec,
    'time'             => gmdate('Y-m-d\TH:i:s\Z'),
], JSON_UNESCAPED_SLASHES);
