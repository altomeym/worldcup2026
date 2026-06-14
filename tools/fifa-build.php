<?php
/**
 * tools/fifa-build.php — مساعد CLI لبناء إحصائيات FIFA (يُستدعى من fifa-extract.ps1).
 *   php tools/fifa-build.php map            → يطبع {رقم المباراة: رابط PDF} (JSON)
 *   php tools/fifa-build.php build <txtdir> → يحلّل ملفّات النصّ ويبني assets/fifa-stats.json
 * يُشغَّل محليّاً فقط (يتطلّب pdftotext لإنتاج ملفّات النصّ — غير متوفّر على Hostinger).
 */
chdir(dirname(__DIR__));
$_SERVER['HTTP_HOST'] = 'wcup2026.org';
require __DIR__ . '/../includes/bootstrap.php';

$cmd = $argv[1] ?? 'build';
if ($cmd === 'map') {
    echo json_encode(FifaReports::reports(), JSON_UNESCAPED_UNICODE);
    exit;
}
$dir = $argv[2] ?? (__DIR__ . '/_fifatxt');
echo 'built ' . FifaStats::build($dir) . " matches\n";
