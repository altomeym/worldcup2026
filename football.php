<?php
/**
 * football.php — تصدير البطولة بصيغة football.txt النصّيّة (openfootball).
 *   /football.php            → الجدول الكامل + النتائج (الملفّ الأساسي)
 *   /football.php?results    → النتائج فقط (المباريات المنتهية)
 *   /football.php?reports    → تقارير المباريات (نتائج + هدّافون + بطاقات)
 *   أضِف &dl لتنزيله كملفّ .txt
 * يُبنى حيّاً من نفس بيانات الموقع، فيبقى متزامناً مع النتائج دائماً.
 */
require __DIR__ . '/includes/bootstrap.php';

if (isset($_GET['results'])) {
    $view = 'results'; $body = FootballTxt::results();
} elseif (isset($_GET['reports'])) {
    $view = 'reports'; $body = FootballTxt::reports();
} else {
    $view = 'schedule'; $body = FootballTxt::schedule();
}

// أفرِغ أيّ مخزّن مؤقّت من bootstrap قبل ضبط الترويسات النصّيّة
if (ob_get_level() > 0) { ob_end_clean(); }

header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: public, max-age=300');
header('Access-Control-Allow-Origin: *');   // بيانات مفتوحة — متاحة للأدوات/LLMs
if (isset($_GET['dl'])) {
    $name = 'worldcup2026' . ($view === 'schedule' ? '' : '-' . $view) . '.txt';
    header('Content-Disposition: attachment; filename="' . $name . '"');
}

echo $body;
