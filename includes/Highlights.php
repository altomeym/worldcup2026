<?php
/**
 * Highlights.php — ملخّصات الفيديو الرسميّة (beIN SPORTS عبر قائمة يوتيوب).
 * ============================================================
 * المصدر: قائمة تشغيل يوتيوب (HIGHLIGHTS_PLAYLIST في config) تُحدَّث بعد
 * كل مباراة بفيديو عنوانه عربي مثل:
 *   «ملخص مباراة المكسيك وجنوب إفريقيا | دور المجموعات - كأس العالم FIFA 2026™»
 *
 * الآليّة (بدون مفتاح YouTube API):
 *   1) كشط صفحة القائمة (كاش 30 دقيقة) → معرّفات الفيديو
 *   2) عنوان كل فيديو جديد عبر oEmbed (يُخزَّن للأبد — طلب واحد لكل فيديو)
 *   3) مطابقة عربيّة ذكيّة: توحيد الهمزات + إسقاط «ال» + إزالة المسافات
 *      («جنوب إفريقيا» تطابق «جنوب أفريقيا»، و«تشيكيا» تطابق «التشيك»)
 *   4) أرشيف دائم لكل مباراة — المطابقة تتمّ مرّة واحدة فقط
 * ============================================================
 */
if (!defined('WC2026')) { exit('Access denied'); }

class Highlights
{
    /** كاش قائمة الفيديوهات بالدقائق (يلتقط الملخّص الجديد خلال ≤30 دقيقة). */
    private const LIST_TTL = 1800;

    /** أقصى عدد عناوين جديدة تُجلب في التحديث الواحد (يحمي زمن الصفحة). */
    private const MAX_NEW_TITLES = 10;

    /** مرادفات عربيّة لفرق تُكتب بأسماء مختلفة في عناوين beIN. */
    private const ALIAS_AR = [
        'USA'           => ['امريكا', 'اميركا', 'الولايات المتحده'],
        'United States' => ['امريكا', 'اميركا', 'الولايات المتحده'],
        'South Korea'   => ['كوريا الجنوبيه'],
        'Czech Republic'=> ['تشيكيا', 'تشيك'],
        'Ivory Coast'   => ['كوت ديفوار', 'ساحل العاج'],
        'Cape Verde'    => ['كاب فيردي', 'الراس الاخضر'],
    ];

    private static function playlistId(): string
    {
        return defined('HIGHLIGHTS_PLAYLIST') && HIGHLIGHTS_PLAYLIST !== ''
            ? HIGHLIGHTS_PLAYLIST
            : 'PLczz3UIGL1Xro9H31oiYmQviSBosVdclk';   // beIN SPORTS — كأس العالم 2026 الرسمية
    }

    // ────────────────────────────────────────────────────────
    //  الواجهة الرئيسية
    // ────────────────────────────────────────────────────────

    /**
     * forMatch($m) — فيديو ملخّص مباراة (للمباريات المنتهية).
     * يعيد ['id' => youtubeId, 'title' => string] أو null لو لم يُنشَر بعد.
     */
    public static function forMatch(array $m): ?array
    {
        $t1 = trim((string)($m['team1'] ?? ''));
        $t2 = trim((string)($m['team2'] ?? ''));
        if ($t1 === '' || $t2 === '') return null;

        // أرشيف دائم — مطابقة واحدة تكفي للأبد
        $key = strtolower(preg_replace('/[^a-z]/i', '', $t1 . $t2));
        $archive = rtrim(CACHE_DIR, '/') . '/match-video-' . md5($key) . '.json';
        if (is_file($archive)) {
            $a = json_decode((string)@file_get_contents($archive), true);
            if (is_array($a) && !empty($a['id'])) return $a;
        }

        // فقط للمباريات المنتهية (الملخّص يُنشَر بعد المباراة)
        $st = $m['_status'] ?? (isset($m['score']['ft']) ? 'finished' : 'upcoming');
        if ($st !== 'finished') return null;

        $needles = array_merge(self::needles($t1), []);
        $needles2 = self::needles($t2);
        if (!$needles || !$needles2) return null;

        foreach (self::videos() as $v) {
            $title = self::normalize((string)($v['title'] ?? ''));
            if ($title === '') continue;
            if (self::containsAny($title, $needles) && self::containsAny($title, $needles2)) {
                $hit = ['id' => (string)$v['id'], 'title' => (string)$v['title']];
                @file_put_contents($archive, json_encode($hit, JSON_UNESCAPED_UNICODE));
                return $hit;
            }
        }
        return null;
    }

    // ────────────────────────────────────────────────────────
    //  جلب القائمة + العناوين
    // ────────────────────────────────────────────────────────

    /** قائمة الفيديوهات [['id','title'], ...] — من الكاش أو بتحديث جديد. */
    public static function videos(): array
    {
        $cacheFile = rtrim(CACHE_DIR, '/') . '/yt-videos.json';
        $stored = [];
        if (is_file($cacheFile)) {
            $d = json_decode((string)@file_get_contents($cacheFile), true);
            if (is_array($d)) $stored = $d;
            if (time() - filemtime($cacheFile) < self::LIST_TTL) return $stored;
        }

        // فشل قريب → لا تعاود الكشط مع كل طلب
        $fail = $cacheFile . '.fail';
        if (is_file($fail) && (time() - filemtime($fail) < 600)) return $stored;

        $ids = self::scrapePlaylistIds();
        if ($ids === null) { @touch($fail); return $stored; }

        // اجلب عناوين الفيديوهات الجديدة فقط (محدودة العدد لكل تحديث)
        $byId = [];
        foreach ($stored as $v) {
            if (!empty($v['id'])) $byId[$v['id']] = $v;
        }
        $fetched = 0;
        foreach ($ids as $id) {
            if (isset($byId[$id])) continue;
            if ($fetched >= self::MAX_NEW_TITLES) break;
            $title = self::oembedTitle($id);
            $fetched++;
            if ($title !== '') $byId[$id] = ['id' => $id, 'title' => $title];
        }

        // رتّب حسب ترتيب القائمة + أبقِ القديمة (القائمة تكبر فقط)
        $out = [];
        foreach ($ids as $id) {
            if (isset($byId[$id])) { $out[] = $byId[$id]; unset($byId[$id]); }
        }
        foreach ($byId as $v) $out[] = $v;   // فيديوهات خرجت من الصفحة الأولى

        if (!is_dir(CACHE_DIR)) @mkdir(CACHE_DIR, 0755, true);
        $tmp = $cacheFile . '.tmp';
        if (@file_put_contents($tmp, json_encode($out, JSON_UNESCAPED_UNICODE)) !== false) {
            @rename($tmp, $cacheFile);
        }
        @unlink($fail);
        return $out;
    }

    /** يكشط صفحة القائمة ويستخرج معرّفات الفيديو بترتيبها. null عند الفشل. */
    private static function scrapePlaylistIds(): ?array
    {
        $url = 'https://www.youtube.com/playlist?list=' . rawurlencode(self::playlistId()) . '&hl=ar';
        $ctx = stream_context_create(['http' => [
            'method'  => 'GET',
            'header'  => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64)\r\n"
                       . "Accept-Language: ar,en;q=0.8\r\n",
            'timeout' => 12,
        ]]);
        $html = @file_get_contents($url, false, $ctx);
        if ($html === false || $html === '') return null;

        if (!preg_match_all('/"videoId":"([A-Za-z0-9_-]{11})"/', $html, $mm)) return [];
        // فريدة مع حفظ الترتيب
        return array_values(array_unique($mm[1]));
    }

    /** عنوان فيديو عبر oEmbed (طلب واحد لكل فيديو — النتيجة تُخزَّن في القائمة). */
    private static function oembedTitle(string $id): string
    {
        $url = 'https://www.youtube.com/oembed?url=' . rawurlencode('https://www.youtube.com/watch?v=' . $id) . '&format=json';
        $ctx = stream_context_create(['http' => ['timeout' => 8, 'header' => "User-Agent: Mozilla/5.0\r\n"]]);
        $raw = @file_get_contents($url, false, $ctx);
        if ($raw === false) return '';
        $d = json_decode($raw, true);
        return is_array($d) ? trim((string)($d['title'] ?? '')) : '';
    }

    // ────────────────────────────────────────────────────────
    //  المطابقة العربيّة
    // ────────────────────────────────────────────────────────

    /** إبر البحث لفريق: الاسم العربي (مع/بدون ال) + الإنجليزي + المرادفات. */
    private static function needles(string $teamEn): array
    {
        $needles = [];
        // الاسم العربي من teams_ar
        if (function_exists('teams_map')) {
            $map = teams_map();
            $ar = trim((string)($map[$teamEn][0] ?? ''));
            if ($ar !== '') {
                $needles[] = self::normalize($ar);
                $needles[] = self::normalize(self::stripAl($ar));
            }
        }
        // الاسم الإنجليزي
        $needles[] = self::normalize($teamEn);
        // مرادفات يدويّة
        foreach (self::ALIAS_AR[$teamEn] ?? [] as $alias) {
            $needles[] = self::normalize($alias);
        }
        // صالحة فقط ما طولها ≥ 3 (تجنّب مطابقات عشوائيّة)
        return array_values(array_unique(array_filter($needles, fn($n) => mb_strlen($n, 'UTF-8') >= 3)));
    }

    /** يحذف «ال» من بداية كل كلمة: «الولايات المتحدة» → «ولايات متحدة». */
    private static function stripAl(string $s): string
    {
        return preg_replace('/(^|\s)ال/u', '$1', $s);
    }

    /**
     * توحيد عربي + لاتيني للمقارنة:
     * أإآ→ا · ة→ه · ى→ي · حذف التشكيل والرموز والمسافات · lowercase لاتيني.
     */
    private static function normalize(string $s): string
    {
        $s = mb_strtolower($s, 'UTF-8');
        $s = str_replace(['أ', 'إ', 'آ'], 'ا', $s);
        $s = str_replace('ة', 'ه', $s);
        $s = str_replace('ى', 'ي', $s);
        $s = preg_replace('/[\x{064B}-\x{065F}\x{0640}]/u', '', $s);   // تشكيل + تطويل
        $s = preg_replace('/[^\p{Arabic}a-z0-9]/u', '', $s);            // إسقاط كل ما عداهما (والمسافات)
        return (string)$s;
    }

    private static function containsAny(string $haystack, array $needles): bool
    {
        foreach ($needles as $n) {
            if ($n !== '' && mb_strpos($haystack, $n, 0, 'UTF-8') !== false) return true;
        }
        return false;
    }
}
