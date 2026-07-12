<?php
/**
 * LiveWatch.php — روابط المشاهدة الرسمية للبث المباشر (فيديو).
 *
 * المصدر: data/watch-links.json (+ WATCH_DEFAULT_URL احتياطي من config).
 * لا يوفّر بثاً من API-Football — روابط خارجية أو iframe YouTube رسمي فقط.
 */
if (!defined('WC2026')) { exit('Access denied'); }

class LiveWatch
{
    private static ?array $data = null;

    /** دقائق قبل kickoff لبدء إظهار قسم «شاهد البث». */
    private const PRE_KICKOFF_MIN = 30;

    private static function load(): array
    {
        if (self::$data !== null) {
            return self::$data;
        }
        $file = __DIR__ . '/../data/watch-links.json';
        $raw  = is_file($file) ? json_decode((string)@file_get_contents($file), true) : null;
        self::$data = is_array($raw) ? $raw : [];
        return self::$data;
    }

    /**
     * shouldShow() — هل نعرض قسم البث؟ (live، أو قبل kickoff بـ 30 دقيقة، وليس منتهياً).
     */
    public static function shouldShow(array $match, ?int $kickoffTs): bool
    {
        $status = $match['_status'] ?? DataService::matchStatus($match);
        if ($status === 'finished') {
            return false;
        }
        if ($status === 'live') {
            return true;
        }
        if ($kickoffTs === null) {
            return false;
        }
        return time() >= ($kickoffTs - self::PRE_KICKOFF_MIN * 60);
    }

    /**
     * forMatch() — روابط المشاهدة + embed اختياري لمباراة.
     *
     * @return array{links: list<array{label:string,url:string,paid:bool,id:string}>, embed: ?string}|null
     */
    public static function forMatch(array $match, int $matchIndex): ?array
    {
        $data    = self::load();
        $lang    = current_lang() === 'ar' ? 'ar' : 'en';
        $entry   = $data['by_match'][(string)$matchIndex] ?? $data['by_match'][$matchIndex] ?? null;
        $default = $data['default'] ?? [];

        $linkDefs = [];
        if (is_array($entry) && !empty($entry['links']) && is_array($entry['links'])) {
            $linkDefs = $entry['links'];
        } elseif (!empty($default['links']) && is_array($default['links'])) {
            $linkDefs = $default['links'];
        } elseif (!empty($default[$lang]['url'])) {
            $linkDefs = [[
                'id'   => 'default',
                'paid' => false,
                $lang  => $default[$lang],
            ]];
        }

        $fallbackUrl = defined('WATCH_DEFAULT_URL') ? trim((string)WATCH_DEFAULT_URL) : '';
        if ($linkDefs === [] && $fallbackUrl !== '') {
            $linkDefs = [[
                'id'   => 'default',
                'paid' => false,
                $lang  => [
                    'label' => $lang === 'ar' ? 'شاهد البث المباشر' : 'Watch live',
                    'url'   => $fallbackUrl,
                ],
            ]];
        }

        if ($linkDefs === []) {
            return null;
        }

        $links = [];
        foreach ($linkDefs as $def) {
            if (!is_array($def)) {
                continue;
            }
            $loc = $def[$lang] ?? $def['en'] ?? $def['ar'] ?? null;
            if (!is_array($loc)) {
                continue;
            }
            $url = trim((string)($loc['url'] ?? ''));
            if ($url === '' || !self::isSafeHttpUrl($url)) {
                continue;
            }
            $links[] = [
                'id'    => (string)($def['id'] ?? 'link'),
                'label' => trim((string)($loc['label'] ?? '')) ?: ($lang === 'ar' ? 'شاهد البث' : 'Watch live'),
                'url'   => $url,
                'paid'  => !empty($def['paid']),
            ];
        }

        if ($links === []) {
            return null;
        }

        $embedRaw = is_array($entry) ? ($entry['embed'] ?? $entry['embed_url'] ?? '') : '';
        $embed    = self::sanitizeEmbedUrl(is_string($embedRaw) ? $embedRaw : '');

        return ['links' => $links, 'embed' => $embed];
    }

    /** يقبل فقط روابط https/http صالحة (لا javascript:). */
    private static function isSafeHttpUrl(string $url): bool
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }
        $scheme = strtolower((string)(parse_url($url, PHP_URL_SCHEME) ?? ''));
        return in_array($scheme, ['http', 'https'], true);
    }

    /**
     * sanitizeEmbedUrl() — iframe YouTube رسمي فقط (youtube / youtube-nocookie embed).
     */
    public static function sanitizeEmbedUrl(?string $url): ?string
    {
        $url = trim((string)$url);
        if ($url === '') {
            return null;
        }

        if (preg_match('#^https://(?:www\.)?youtube-nocookie\.com/embed/([A-Za-z0-9_-]{11})#', $url, $m)) {
            return 'https://www.youtube-nocookie.com/embed/' . $m[1];
        }
        if (preg_match('#^https://(?:www\.)?youtube\.com/embed/([A-Za-z0-9_-]{11})#', $url, $m)) {
            return 'https://www.youtube-nocookie.com/embed/' . $m[1];
        }
        if (preg_match('#(?:youtube\.com/watch\?v=|youtu\.be/)([A-Za-z0-9_-]{11})#', $url, $m)) {
            return 'https://www.youtube-nocookie.com/embed/' . $m[1];
        }

        return null;
    }
}
