<?php
/**
 * api/visit.php — عدّاد زوّار بسيط (ملفّي).
 * ============================================================
 * GET → يزيد العدّاد مرّة واحدة لكل زائر (عبر كوكي خفيف لمدّة 6 ساعات)،
 *        ويُرجّع الإجمالي. يبدأ من قاعدة مبدئية VISIT_BASE.
 *
 * يُستدعى من JS بعد تحميل الصفحة (يعمل حتى مع كاش الصفحات الكامل، لأن الـHTML
 * المخزّن يحوي السكربت الذي يُنفَّذ في كل تحميل). الرد بلا تخزين (no-store).
 * التخزين: data/_visits.json بكتابة ذرّية (flock) كباقي العدّادات.
 * ============================================================
 */
require __DIR__ . '/../includes/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

const VISIT_BASE = 12305;   // القاعدة المبدئية للعدّاد

$file = rtrim(CACHE_DIR, '/') . '/../data/_visits.json';

/** يقرأ العدّاد الحالي (لا يقلّ عن القاعدة). */
function visit_count(string $file): int
{
    $d = json_decode((string)@file_get_contents($file), true);
    $c = (is_array($d) && isset($d['count'])) ? (int)$d['count'] : 0;
    return max($c, VISIT_BASE);
}

$alreadyCounted = isset($_COOKIE['wc_seen']);
$count = visit_count($file);

if (!$alreadyCounted) {
    $dir = dirname($file);
    if (!is_dir($dir)) { @mkdir($dir, 0755, true); }
    $fp = @fopen($file, 'c+b');
    if ($fp) {
        @flock($fp, LOCK_EX);
        rewind($fp);
        $d   = json_decode((string)stream_get_contents($fp), true);
        $cur = (is_array($d) && isset($d['count'])) ? (int)$d['count'] : 0;
        if ($cur < VISIT_BASE) { $cur = VISIT_BASE; }   // ابدأ من القاعدة
        $cur++;
        rewind($fp);
        ftruncate($fp, 0);
        fwrite($fp, json_encode(['count' => $cur]));
        fflush($fp);
        @flock($fp, LOCK_UN);
        fclose($fp);
        $count = $cur;

        if (!headers_sent()) {
            setcookie('wc_seen', '1', [
                'expires'  => time() + 6 * 3600,
                'path'     => '/',
                'secure'   => (stripos((string)SITE_URL, 'https://') === 0),
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
        }
    }
}

echo json_encode(['count' => $count], JSON_UNESCAPED_UNICODE);
