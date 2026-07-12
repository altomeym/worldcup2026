<?php
/**
 * cron/deploy.php — مزامنة إحصائيات FIFA من GitHub إلى الإنتاج (بلا git ولا hPanel).
 * ============================================================
 * يكمّل سلسلة التحديث الآلي للتقارير:
 *   جهازك (يستخرج + git push) → GitHub → هذا السكربت (يسحب assets/fifa) → الموقع محدَّث.
 * يعمل على أي استضافة: يجلب ملفّات assets/fifa/*.json عبر HTTP من GitHub (الريبو عام)
 * ويكتب الجديد/المتغيّر فقط. لا يحتاج git ولا exec ولا لوحة Hostinger.
 *
 * أضِفه في Cron كل ساعة (إحصائيات FIFA فقط — لا ينشر الكود):
 *   php /home/USER/public_html/cron/deploy.php
 *   أو: curl "https://example.com/cron/deploy.php?token=INSTALL_TOKEN"
 * نشر الكود الكامل من GitHub: أضف &code=1 (يدوياً فقط — ليس في Cron).
 * ============================================================
 */
require __DIR__ . '/../includes/bootstrap.php';
while (ob_get_level() > 0) { ob_end_clean(); }

if (PHP_SAPI !== 'cli') {
    $tok = (string)($_GET['token'] ?? '');
    if (!defined('INSTALL_TOKEN') || INSTALL_TOKEN === '' || !hash_equals(INSTALL_TOKEN, $tok)) {
        http_response_code(403); exit('forbidden');
    }
    header('Content-Type: text/plain; charset=utf-8');
}

const GH_API = 'https://api.github.com/repos/salah23222/worldcup2026/contents/assets/fifa';

$listJson = http_get(GH_API, ['timeout' => 15, 'ua' => 'wcup2026-deploy', 'redirects' => true]);
if ($listJson === null) { echo "GitHub list unreachable\n"; exit; }
$files = json_decode($listJson, true);
if (!is_array($files)) { echo "GitHub list invalid: " . substr((string)$listJson, 0, 200) . "\n"; exit; }

$dir = __DIR__ . '/../assets/fifa';
if (!is_dir($dir)) @mkdir($dir, 0755, true);

$added = 0; $skip = 0;
foreach ($files as $f) {
    if (($f['type'] ?? '') !== 'file') continue;
    $name = basename((string)($f['name'] ?? ''));
    if (!preg_match('/^[a-f0-9]{32}\.json$/', $name)) continue;       // أمان: hash.json فقط
    $local = $dir . '/' . $name;
    clearstatcache(true, $local);
    if (is_file($local) && (int)@filesize($local) === (int)($f['size'] ?? -1)) { $skip++; continue; }
    $raw = http_get((string)($f['download_url'] ?? ''), ['timeout' => 15, 'redirects' => true]);
    if ($raw !== null && json_decode($raw) !== null) {
        if (@file_put_contents($local . '.tmp', $raw) !== false) { @rename($local . '.tmp', $local); $added++; }
    }
}
echo "fifa sync — added/updated: $added, up-to-date: $skip\n";

// ملاحظة: fifa-photos.json / fifa-metrics.json / fifa-motm.json تُحدَّث عبر cron/fifa-feed.php
// (مصدر مباشر fifaphy) — لا تُسحَب هنا من GitHub لتجنّب التعارض.

// ===== النشر الذاتي الكامل للكود (يدوياً فقط: ?code=1) =====
// يسحب أرشيف الريبو من GitHub ويكتب الملفّات المتغيّرة فقط فوق جذر الموقع.
// لا يُفعَّل من CLI/Cron — FIFA sync أعلاه فقط. أمان: لا يحذف · لا يلمس config.local.php · لا data/
if (($_GET['code'] ?? '') === '1') {
    echo deploy_pull_code() . "\n";
}

function deploy_pull_code(): string
{
    if (!class_exists('ZipArchive')) return 'code — ZipArchive غير متوفّر على الخادم، تخطّي';

    $zipUrl = 'https://codeload.github.com/salah23222/worldcup2026/zip/refs/heads/main';
    $bytes  = http_get($zipUrl, ['timeout' => 90, 'ua' => 'wcup2026-deploy', 'redirects' => true]);
    if ($bytes === null || strlen($bytes) < 2000) return 'code — فشل تنزيل الأرشيف من GitHub';

    $tmpZip = tempnam(sys_get_temp_dir(), 'wcz');
    if ($tmpZip === false || @file_put_contents($tmpZip, $bytes) === false) return 'code — تعذّر حفظ الأرشيف مؤقّتاً';

    $za = new ZipArchive();
    if ($za->open($tmpZip) !== true) { @unlink($tmpZip); return 'code — تعذّر فتح الأرشيف'; }

    $root      = dirname(__DIR__);   // جذر الموقع (public_html)
    $PROTECT   = ['config.local.php', 'includes/config.local.php'];      // لا تُلمَس أبداً
    $SKIP_TOP  = ['data', '.git', '.github', '.gitignore', 'README.md']; // لا تُنشَر
    $updated = 0; $same = 0; $skipped = 0;

    for ($i = 0; $i < $za->numFiles; $i++) {
        $entry = (string)$za->getNameIndex($i);
        if ($entry === '' || substr($entry, -1) === '/') continue;          // مدخل مجلّد
        $rel = preg_replace('#^[^/]+/#', '', $entry);                        // انزع مجلّد الجذر
        if ($rel === '' || strpos($rel, '..') !== false) { $skipped++; continue; }   // أمان المسار
        $top = explode('/', $rel)[0];
        if (in_array($top, $SKIP_TOP, true) || in_array($rel, $PROTECT, true)) { $skipped++; continue; }

        $content = $za->getFromIndex($i);
        if ($content === false) { $skipped++; continue; }

        $target = $root . '/' . $rel;
        clearstatcache(true, $target);
        if (is_file($target) && md5($content) === (string)@md5_file($target)) { $same++; continue; }

        $tdir = dirname($target);
        if (!is_dir($tdir)) @mkdir($tdir, 0755, true);
        if (@file_put_contents($target . '.tmp', $content) !== false && @rename($target . '.tmp', $target)) {
            $updated++;
        } else {
            $skipped++;
        }
    }
    $za->close();
    @unlink($tmpZip);
    return "code — حُدّث: $updated ملفّ · بلا تغيير: $same · تُخطّي: $skipped";
}
