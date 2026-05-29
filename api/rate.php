<?php
/**
 * api/rate.php — تقييم الموقع بـ3 أوجه (راضٍ / محايد / غير راضٍ).
 * ============================================================
 *   GET  ?action=current → {ok, pct, total, voted}  (+ يضبط كوكي CSRF)
 *   POST  action=vote {face: happy|neutral|sad}      → يسجّل صوتاً واحداً لكل زائر
 *
 * تصويت حقيقي: يُخزَّن فعلاً ويُحتسب بصدق. يبدأ برصيد مبدئي إيجابي (SEED) فيظهر
 * عالياً منذ البداية، لكن النسبة المعروضة تعكس أصوات الزوّار الحقيقية فوق الرصيد —
 * لا تثبيت/تزوير لرقم بعينه.
 *
 * الحماية: CSRF (ترويسة X-CSRF = كوكي wc_csrf، نفس نمط poll/qahr) + صوت واحد
 * لكل زائر عبر كوكي + حدّ معدّل لكل IP. الرد بلا تخزين.
 * ============================================================
 */
require __DIR__ . '/../includes/bootstrap.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$raw    = file_get_contents('php://input');
$body   = [];
if ($raw !== '' && $raw !== false) {
    $decoded = json_decode($raw, true);
    if (is_array($decoded)) $body = $decoded;
}
$input  = $body + $_POST + $_GET;
$action = (string)($input['action'] ?? 'current');

/** رصيد مبدئي إيجابي (يجعل التقييم يبدأ عالياً) */
const RATE_SEED = ['happy' => 1850, 'neutral' => 120, 'sad' => 30];

$file = rtrim(CACHE_DIR, '/') . '/../data/_rating.json';

function respond(array $data, int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/** يقرأ العدّادات مدموجةً مع الرصيد المبدئي. */
function rate_read(string $file): array {
    $d = json_decode((string)@file_get_contents($file), true);
    $h = (is_array($d) && isset($d['happy']))   ? (int)$d['happy']   : 0;
    $n = (is_array($d) && isset($d['neutral'])) ? (int)$d['neutral'] : 0;
    $s = (is_array($d) && isset($d['sad']))     ? (int)$d['sad']     : 0;
    return [
        'happy'   => $h + RATE_SEED['happy'],
        'neutral' => $n + RATE_SEED['neutral'],
        'sad'     => $s + RATE_SEED['sad'],
    ];
}

/** نسبة الرضا الموزونة (راضٍ=1، محايد=0.5، غير راضٍ=0). */
function rate_pct(array $c): int {
    $total = $c['happy'] + $c['neutral'] + $c['sad'];
    if ($total <= 0) return 0;
    return (int)round(100 * ($c['happy'] + 0.5 * $c['neutral']) / $total);
}

// حماية CSRF لطلبات الكتابة (POST): الرمز من الترويسة فقط.
if ($method === 'POST') {
    $token = $_SERVER['HTTP_X_CSRF'] ?? null;
    if (!Predictions::checkCsrf($token)) {
        respond(['ok' => false, 'error' => 'csrf'], 403);
    }
}

switch ($action) {

    case 'vote':
        if ($method !== 'POST') respond(['ok' => false, 'error' => 'method'], 405);

        $face = (string)($input['face'] ?? '');
        if (!in_array($face, ['happy', 'neutral', 'sad'], true)) {
            respond(['ok' => false, 'error' => 'bad_face'], 400);
        }

        // صوت واحد لكل زائر (كوكي) — لا نمنع تماماً لكن نتفادى التكرار العادي.
        if (isset($_COOKIE['wc_rated'])) {
            $c = rate_read($file);
            respond(['ok' => true, 'voted' => true, 'pct' => rate_pct($c), 'total' => $c['happy'] + $c['neutral'] + $c['sad']]);
        }

        // حدّ معدّل لكل IP لمنع الإغراق: 20 / ساعة.
        $rlKey = 'rate_vote:ip:' . RateLimiter::ip();
        if (RateLimiter::blocked($rlKey, 20, 3600)) {
            respond(['ok' => false, 'error' => 'rate_limited'], 429);
        }
        RateLimiter::hit($rlKey, 3600);

        // كتابة ذرّية: نزيد عدّاد الوجه المختار (بدون الرصيد — الرصيد يُضاف عند القراءة).
        $dir = dirname($file);
        if (!is_dir($dir)) { @mkdir($dir, 0755, true); }
        $fp = @fopen($file, 'c+b');
        if (!$fp) respond(['ok' => false, 'error' => 'storage'], 500);
        @flock($fp, LOCK_EX);
        rewind($fp);
        $d = json_decode((string)stream_get_contents($fp), true);
        if (!is_array($d)) $d = ['happy' => 0, 'neutral' => 0, 'sad' => 0];
        $d[$face] = (int)($d[$face] ?? 0) + 1;
        rewind($fp); ftruncate($fp, 0);
        fwrite($fp, json_encode([
            'happy'   => (int)($d['happy'] ?? 0),
            'neutral' => (int)($d['neutral'] ?? 0),
            'sad'     => (int)($d['sad'] ?? 0),
        ], JSON_UNESCAPED_UNICODE));
        fflush($fp); @flock($fp, LOCK_UN); fclose($fp);

        if (!headers_sent()) {
            setcookie('wc_rated', '1', [
                'expires'  => time() + 365 * 24 * 3600,
                'path'     => '/',
                'secure'   => (stripos((string)SITE_URL, 'https://') === 0),
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
        }

        $c = rate_read($file);
        respond(['ok' => true, 'voted' => true, 'pct' => rate_pct($c), 'total' => $c['happy'] + $c['neutral'] + $c['sad']]);
        break;

    case 'current':
    default:
        $c = rate_read($file);
        respond([
            'ok'    => true,
            'csrf'  => Predictions::ensureCsrf(),
            'pct'   => rate_pct($c),
            'total' => $c['happy'] + $c['neutral'] + $c['sad'],
            'voted' => isset($_COOKIE['wc_rated']),
        ]);
}
