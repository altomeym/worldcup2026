<?php
/**
 * Insights.php — مقالات تحليلية أصلية (foot-boll) من data/insights.json.
 */
if (!defined('WC2026')) { exit('Access denied'); }

class Insights
{
    private static ?array $items = null;

    private static function load(): array
    {
        if (self::$items !== null) return self::$items;
        $f = __DIR__ . '/../data/insights.json';
        $d = is_file($f) ? json_decode((string)@file_get_contents($f), true) : [];
        self::$items = is_array($d) ? $d : [];
        return self::$items;
    }

    /** كل المقالات المنشورة مرتّبة (الأحدث أولاً). */
    public static function all(): array
    {
        $today = date('Y-m-d');
        $out = [];
        foreach (self::load() as $it) {
            if (!is_array($it) || empty($it['slug'])) continue;
            $pub = (string)($it['published'] ?? '');
            if ($pub !== '' && $pub > $today) continue;
            $out[] = $it;
        }
        usort($out, fn($a, $b) => strcmp((string)($b['published'] ?? ''), (string)($a['published'] ?? '')));
        return $out;
    }

    public static function find(string $slug): ?array
    {
        $slug = trim($slug);
        if ($slug === '') return null;
        foreach (self::all() as $it) {
            if (($it['slug'] ?? '') === $slug) return $it;
        }
        return null;
    }

    public static function field(array $it, string $key, ?string $lang = null): string
    {
        $lang = ($lang === 'ar' || $lang === 'en') ? $lang : current_lang();
        $k = $key . '_' . $lang;
        if (!empty($it[$k])) return (string)$it[$k];
        $fallback = $key . '_en';
        return (string)($it[$fallback] ?? '');
    }

    /** فقرات النص الأساسي. */
    public static function body(array $it, ?string $lang = null): array
    {
        $lang = ($lang === 'ar' || $lang === 'en') ? $lang : current_lang();
        $k = 'body_' . $lang;
        $raw = $it[$k] ?? $it['body_en'] ?? [];
        return is_array($raw) ? array_values(array_filter($raw, fn($p) => trim((string)$p) !== '')) : [];
    }

    public static function url(array $it): string
    {
        return url('insight.php', ['slug' => (string)($it['slug'] ?? '')]);
    }

    /** أحدث مقال (للصفحة الرئيسية). */
    public static function latest(): ?array
    {
        $all = self::all();
        return $all[0] ?? null;
    }
}
