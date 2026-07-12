<?php
/**
 * FlagProxy — خادم وسيط لصور الأعلام.
 * flagcdn.com قد يُحجَب في بعض الدول؛ المتصفّح يطلب flag.php من نفس النطاق،
 * والخادم يجلب الصورة من CDN (مع بدائل) ويخزّنها محلياً.
 */
if (!defined('WC2026')) { exit('Access denied'); }

/** أحجام flagcdn المدعومة */
function flag_valid_size(string $size): string {
    static $ok = ['w20', 'w40', 'w80', 'w160', 'w320'];
    return in_array($size, $ok, true) ? $size : 'w80';
}

/** تحقق من رمز العلم (mx, gb-eng, …) */
function flag_valid_code(string $code): string {
    $code = strtolower(trim($code));
    if (!preg_match('/^[a-z]{2}(-[a-z]{3})?$/', $code)) {
        return '';
    }
    return $code;
}

/** مسار URL للبروكسي (نفس نطاق الموقع — يعمل بدون VPN) */
function flag_proxy_url(string $code, string $size = 'w80'): string {
    $code = flag_valid_code($code);
    if ($code === '') {
        return '';
    }
    $size = flag_valid_size($size);
    $root = function_exists('base_url') ? rtrim(base_url(), '/') : '';
    return $root . '/flag.php?s=' . rawurlencode($size) . '&c=' . rawurlencode($code);
}

/** رابط مباشر لـ flagcdn (للجلب من الخادم فقط) */
function flag_cdn_url(string $code, string $size = 'w80'): string {
    return 'https://flagcdn.com/' . flag_valid_size($size) . '/' . flag_valid_code($code) . '.png';
}

function flag_size_to_px(string $size): int {
    return match (flag_valid_size($size)) {
        'w20'  => 24,
        'w40'  => 32,
        'w80'  => 64,
        'w160' => 128,
        'w320' => 256,
        default => 64,
    };
}

/** مصادر بديلة — تُجرَّب بالترتيب عند الجلب من الخادم */
function flag_upstream_urls(string $code, string $size): array {
    $c   = flag_valid_code($code);
    $sz  = flag_valid_size($size);
    $px  = flag_size_to_px($sz);
    // jsDelivr أولاً — غالباً غير محجوب وأسرع في المنطقة العربية
    $urls = [
        'https://cdn.jsdelivr.net/npm/flag-icons@7.2.3/flags/4x3/' . $c . '.svg',
        'https://flagcdn.com/' . $sz . '/' . $c . '.png',
    ];

    if (preg_match('/^[a-z]{2}$/', $c)) {
        $u = strtoupper($c);
        $urls[] = 'https://flagsapi.com/' . $u . '/flat/' . $px . '.png';
        $urls[] = 'https://countryflagsapi.com/png/' . $px . '/' . $u;
    }

    $urls[] = 'https://cdn.jsdelivr.net/npm/flag-icons@7.2.3/flags/1x1/' . $c . '.svg';

    return $urls;
}

function flag_cache_dir(): string {
    $dir = rtrim(CACHE_DIR, '/\\') . '/flags';
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
    return $dir;
}

function flag_cache_file(string $code, string $size, string $ext): string {
    $safe = preg_replace('/[^a-z0-9-]/', '', flag_valid_code($code));
    return flag_cache_dir() . '/' . flag_valid_size($size) . '_' . $safe . '.' . $ext;
}

/** @return array{body:string,type:string,ext:string}|null */
function flag_fetch_remote(string $code, string $size): ?array {
    if (!function_exists('http_get')) {
        require_once __DIR__ . '/helpers.php';
    }

    foreach (flag_upstream_urls($code, $size) as $url) {
        $body = http_get($url, ['timeout' => 5, 'redirects' => true]);
        if ($body === null || strlen($body) < 80) {
            continue;
        }

        $isSvg = str_ends_with(strtolower(parse_url($url, PHP_URL_PATH) ?: ''), '.svg')
              || str_starts_with(ltrim($body), '<svg')
              || str_starts_with(ltrim($body), '<?xml');

        if ($isSvg) {
            if (!str_contains($body, '<svg')) {
                continue;
            }
            return ['body' => $body, 'type' => 'image/svg+xml', 'ext' => 'svg'];
        }

        if (str_starts_with($body, "\x89PNG\r\n\x1a\n")) {
            return ['body' => $body, 'type' => 'image/png', 'ext' => 'png'];
        }
    }

    return null;
}

/** يقرأ من الكاش أو يجلب ويخزّن */
function flag_resolve(string $code, string $size): ?array {
    $code = flag_valid_code($code);
    if ($code === '') {
        return null;
    }
    $size = flag_valid_size($size);
    $ttl  = defined('FLAG_CACHE_TTL') ? (int)FLAG_CACHE_TTL : 2592000;

    foreach (['png', 'svg'] as $ext) {
        $file = flag_cache_file($code, $size, $ext);
        if (is_file($file) && (time() - filemtime($file)) < $ttl) {
            $body = @file_get_contents($file);
            if ($body !== false && $body !== '') {
                return [
                    'body' => $body,
                    'type' => $ext === 'svg' ? 'image/svg+xml' : 'image/png',
                    'ext'  => $ext,
                ];
            }
        }
    }

    $fetched = flag_fetch_remote($code, $size);
    if ($fetched === null) {
        return null;
    }

    @file_put_contents(flag_cache_file($code, $size, $fetched['ext']), $fetched['body'], LOCK_EX);
    return $fetched;
}

/** يرسل الصورة للمتصفّح وينهي الطلب */
function flag_proxy_serve(string $code, string $size): void {
    $resolved = flag_resolve($code, $size);
    if ($resolved === null) {
        http_response_code(404);
        header('Content-Type: text/plain; charset=UTF-8');
        header('Cache-Control: public, max-age=300');
        echo 'Flag not found';
        exit;
    }

    $maxAge = defined('FLAG_CACHE_TTL') ? (int)FLAG_CACHE_TTL : 2592000;
    header('Content-Type: ' . $resolved['type']);
    header('Cache-Control: public, max-age=' . $maxAge . ', immutable');
    header('X-Content-Type-Options: nosniff');
    echo $resolved['body'];
    exit;
}
