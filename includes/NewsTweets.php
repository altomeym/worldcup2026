<?php
/**
 * NewsTweets.php — نشر تلقائي لأخبار كأس العالم على X (AR + EN).
 * ============================================================
 * عند كل تشغيل cron:
 *   • يجلب أحدث الأخبار AR + EN من News::latest($lang)
 *   • يُرشّح الذي لم يُنشَر بعد (يستخدم md5 للرابط معرّفاً)
 *   • ينشر حتى تغريدتَين في كل run (1 AR + 1 EN) — تجنّباً للسبام
 *   • ينشر فقط بين 08:00–23:00 (لا أخبار منتصف الليل)
 *   • يحفظ كل id منشور في cache/x_news_state.json (آخر 300)
 *
 * كل تغريدة تربط بـ /news.php (الترافيك يعود للموقع، لا للمصدر الخارجي).
 * ============================================================
 */
if (!defined('WC2026')) { exit('Access denied'); }

class NewsTweets
{
    /** أقصى عدد تغريدات أخبار في كل run cron (بغضّ النظر عن اللغة). */
    public const MAX_PER_RUN = 2;

    /** نافذة النشر بالساعات (Asia/Dubai). نتجنّب 00:00–07:59. */
    private const HOUR_FROM = 8;
    private const HOUR_TO   = 23;   // شامل

    /** أقصى أخبار نخزّن معرفاتها (تجنّب نموّ ملف الحالة). */
    private const STATE_MAX = 300;

    // ──────────────────────── واجهات الـ cron ────────────────────────

    /** هل نحن في نافذة وقت النشر؟ */
    public static function inWindow(int $now = 0): bool
    {
        $h = (int)date('G', $now ?: time());
        return ($h >= self::HOUR_FROM && $h <= self::HOUR_TO);
    }

    /** قائمة أخبار جاهزة للنشر (لكلتا اللغتين)، أحدث أوّلاً. */
    public static function pending(): array
    {
        $seen = self::seen();
        $jobs = [];
        foreach (['ar', 'en'] as $lang) {
            if (!class_exists('News')) break;
            $items = News::latest(12, $lang);
            foreach ($items as $it) {
                if (empty($it['title']) || empty($it['link'])) continue;
                $id = !empty($it['id']) ? (string)$it['id'] : substr(md5((string)$it['link']), 0, 12);
                if (isset($seen[$id])) continue;
                $jobs[] = ['lang' => $lang, 'item' => $it, 'id' => $id, 'ts' => (int)($it['ts'] ?? 0)];
            }
        }
        // أحدث أوّلاً
        usort($jobs, fn($a, $b) => $b['ts'] <=> $a['ts']);
        return $jobs;
    }

    /** ينشر تغريدة لخبر واحد (يبني → يرسل → يسجّل). */
    public static function sendOne(array $item, string $lang, string $id): array
    {
        $text = self::buildTweet($item, $lang);
        $r = XPublisher::tweet($text);
        if ($r['ok']) self::markSent($id, $lang, (string)$item['title'], (string)$r['id']);
        return $r + ['text' => $text];
    }

    // ──────────────────────── بانية النصّ ────────────────────────

    public static function buildTweet(array $it, string $lang): string
    {
        $ar     = ($lang === 'ar');
        $title  = trim((string)($it['title'] ?? ''));
        $source = trim((string)($it['source'] ?? ''));
        if ($source === '' && !empty($it['host'])) {
            $source = self::prettyHost((string)$it['host']);
        }
        $url  = self::link("news.php?lang={$lang}");
        $tags = defined('X_HASHTAGS') ? X_HASHTAGS : '#FIFAWorldCup26';

        $head = $ar ? "📰 خبر جديد" : "📰 News";
        if ($source !== '') {
            $head .= $ar ? " · المصدر: {$source}" : " · Source: {$source}";
        }

        // ميزانية الحروف لعنوان الخبر: 280 - (head + url + tags + 3 سطور فاصلة)
        $fixed = mb_strlen($head, 'UTF-8') + mb_strlen($url, 'UTF-8') + mb_strlen($tags, 'UTF-8') + 4;
        $budget = max(50, 280 - $fixed);
        if (mb_strlen($title, 'UTF-8') > $budget) {
            $title = mb_substr($title, 0, $budget - 1, 'UTF-8') . '…';
        }

        return $head . "\n" . $title . "\n" . $url . "\n" . $tags;
    }

    // ──────────────────────── الحالة ────────────────────────

    private static function stateFile(): string
    {
        return rtrim(CACHE_DIR, '/') . '/x_news_state.json';
    }

    /** map: id => {ts, tweet_id, title, lang}  — للتحقّق السريع من التكرار */
    public static function seen(): array
    {
        $f = self::stateFile();
        if (!is_file($f)) return [];
        $d = json_decode((string)@file_get_contents($f), true);
        return is_array($d) ? $d : [];
    }

    public static function markSent(string $id, string $lang, string $title, string $tweetId): void
    {
        $s = self::seen();
        $s[$id] = [
            'ts'       => time(),
            'tweet_id' => $tweetId,
            'title'    => mb_substr($title, 0, 160, 'UTF-8'),
            'lang'     => $lang,
        ];
        // اقتصار الحجم: أبقِ الأحدث STATE_MAX فقط
        if (count($s) > self::STATE_MAX) {
            uasort($s, fn($a, $b) => ($b['ts'] ?? 0) <=> ($a['ts'] ?? 0));
            $s = array_slice($s, 0, self::STATE_MAX, true);
        }
        if (!is_dir(CACHE_DIR)) @mkdir(CACHE_DIR, 0755, true);
        @file_put_contents(self::stateFile(), json_encode($s, JSON_UNESCAPED_UNICODE));
    }

    public static function recentLog(int $n = 12): array
    {
        $s = self::seen();
        uasort($s, fn($a, $b) => ($b['ts'] ?? 0) <=> ($a['ts'] ?? 0));
        $rows = [];
        foreach (array_slice($s, 0, $n, true) as $id => $v) {
            $rows[] = [
                'id'    => $id,
                'lang'  => $v['lang']     ?? '?',
                'title' => $v['title']    ?? '—',
                'tweet' => $v['tweet_id'] ?? '',
                'at'    => (int)($v['ts'] ?? 0),
            ];
        }
        return $rows;
    }

    // ──────────────────────── مساعدات ────────────────────────

    private static function prettyHost(string $host): string
    {
        $h = preg_replace('/^www\./', '', strtolower(trim($host)));
        // اقتطع أوّل تسمية (مثلاً bbc من bbc.com)
        return $h !== '' ? $h : '';
    }

    private static function link(string $path = ''): string
    {
        $base = defined('SITE_URL') && SITE_URL !== '' ? rtrim(SITE_URL, '/') : 'https://wcup2026.org';
        return $base . '/' . ltrim($path, '/');
    }
}
