<?php
/**
 * EspnLive.php — مصدر نتائج لحظية مجاني (واجهة ESPN العامة غير الموثّقة).
 * ============================================================
 * بديل تلقائي عندما يفشل API-Football أو لا تدعم خطته الموسم
 * (الخطة المجانية لا تدعم 2026). بلا مفتاح، بلا تسجيل، بلا حدود معلنة.
 *
 * يعيد نفس صيغة LiveService::fetchLive تماماً:
 *   key = normalizeKey(home, away) →
 *   ['home','away','ft'=>[g1,g2],'elapsed','status','short','fixture_id','referee']
 *
 * يوفّر أيضاً (عبر summary endpoint):
 *   statsFor()  — إحصائيات المباراة (استحواذ/تسديدات/أخطاء/بطاقات...)
 *   lineupFor() — التشكيلة الرسمية (تظهر قبل الانطلاق بساعة تقريباً)
 *
 * ملاحظة أسماء: ESPN تكتب "Czechia"/"USA" — أسماء openfootball تختلف،
 * والمطابقة تتم عبر LiveService::normalizeKey التي تتكفّل بالمرادفات.
 * ============================================================
 */
if (!defined('WC2026')) { exit('Access denied'); }

class EspnLive
{
    private const URL = 'https://site.api.espn.com/apis/site/v2/sports/soccer/fifa.world/scoreboard';

    /**
     * fetchLive() — يجلب مباريات اليوم من ESPN ويحوّلها لصيغة LiveService.
     * null = فشل الجلب/التحليل. [] = نجاح بلا مباريات اليوم.
     */
    public static function fetchLive(): ?array
    {
        $timeout = defined('FETCH_TIMEOUT') ? max(1, (int)FETCH_TIMEOUT) : 5;
        $raw = function_exists('http_get') ? http_get(self::URL, ['timeout' => $timeout]) : null;
        if ($raw === null) return null;

        $j = json_decode($raw, true);
        if (!is_array($j) || !isset($j['events']) || !is_array($j['events'])) return null;

        $out = [];
        foreach ($j['events'] as $ev) {
            $comp = $ev['competitions'][0] ?? null;
            if (!is_array($comp)) continue;

            $home = $away = null;
            foreach (($comp['competitors'] ?? []) as $c) {
                if (!is_array($c)) continue;
                if (($c['homeAway'] ?? '') === 'home') $home = $c;
                elseif (($c['homeAway'] ?? '') === 'away') $away = $c;
            }
            if (!$home || !$away) continue;

            $hn = trim((string)($home['team']['name'] ?? ''));
            $an = trim((string)($away['team']['name'] ?? ''));
            if ($hn === '' || $an === '') continue;

            // الحالة: pre = لم تبدأ · in = جارية · post = انتهت
            $state  = strtolower((string)($ev['status']['type']['state'] ?? 'pre'));
            $status = ($state === 'in') ? 'live' : (($state === 'post') ? 'finished' : 'upcoming');

            // دقيقة اللعب من displayClock مثل "67'"
            $elapsed = null;
            if (preg_match('/(\d+)/', (string)($ev['status']['displayClock'] ?? ''), $mm)) {
                $elapsed = (int)$mm[1];
            }

            $key = LiveService::normalizeKey($hn, $an);
            $out[$key] = [
                'home'       => $hn,
                'away'       => $an,
                'ft'         => [(int)($home['score'] ?? 0), (int)($away['score'] ?? 0)],
                'elapsed'    => $elapsed,
                'status'     => $status,
                'short'      => (string)($ev['status']['type']['shortDetail'] ?? ''),
                'fixture_id' => null,    // خاص بـ API-Football — غير متاح هنا
                'espn_id'    => (string)($ev['id'] ?? ''),   // 🆕 لجلب الإحصائيات/التشكيلة
                'referee'    => null,
                '_src'       => 'espn',
            ];
        }
        return $out;
    }

    // ════════════════════════════════════════════════════════════
    //  🆕 summary — إحصائيات + تشكيلات (مجاني، بدون مفتاح)
    // ════════════════════════════════════════════════════════════

    private const SUMMARY = 'https://site.api.espn.com/apis/site/v2/sports/soccer/fifa.world/summary?event=';

    /** يجلب summary مباراة (مع كاش LIVE_CACHE_TTL + fail-marker). */
    private static function summary(string $eventId): ?array
    {
        $eventId = trim($eventId);
        if ($eventId === '' || !preg_match('/^\d+$/', $eventId)) return null;

        $cacheFile = rtrim(CACHE_DIR, '/') . '/espn-sum-' . $eventId . '.json';
        $ttl = defined('LIVE_CACHE_TTL') ? max(30, (int)LIVE_CACHE_TTL) : 60;
        $stale = null;
        if (is_file($cacheFile)) {
            $c = json_decode((string)@file_get_contents($cacheFile), true);
            if (is_array($c)) {
                if (time() - filemtime($cacheFile) < $ttl) return $c;
                $stale = $c;
            }
        }
        $fail = $cacheFile . '.fail';
        if (is_file($fail) && (time() - filemtime($fail) < 120)) return $stale;

        $timeout = defined('FETCH_TIMEOUT') ? max(1, (int)FETCH_TIMEOUT) : 5;
        $raw = function_exists('http_get') ? http_get(self::SUMMARY . $eventId, ['timeout' => $timeout]) : null;
        if ($raw === null) { @touch($fail); return $stale; }
        $j = json_decode($raw, true);
        if (!is_array($j)) { @touch($fail); return $stale; }

        if (!is_dir(CACHE_DIR)) @mkdir(CACHE_DIR, 0755, true);
        $tmp = $cacheFile . '.tmp';
        if (@file_put_contents($tmp, json_encode($j, JSON_UNESCAPED_UNICODE)) !== false) {
            @rename($tmp, $cacheFile);
        }
        @unlink($fail);
        return $j;
    }

    /** يحدّد أيّ team.id في boxscore هو المضيف (من header.competitions). */
    private static function homeTeamId(array $j): string
    {
        foreach (($j['header']['competitions'][0]['competitors'] ?? []) as $c) {
            if (($c['homeAway'] ?? '') === 'home') return (string)($c['team']['id'] ?? '');
        }
        return '';
    }

    /**
     * statsFor($eventId) — إحصائيات بصيغة LiveService::statsFor نفسها:
     *   [['k'=>عربي, 'k_en'=>EN, 'v'=>[home,away], 'unit'=>''|'%'], ...]
     * ترتيب v دائماً [مضيف, ضيف] — مَن يستدعيها يتكفّل بالعكس عند الحاجة.
     */
    public static function statsFor(string $eventId): array
    {
        $j = self::summary($eventId);
        if (!is_array($j)) return [];
        $teams = $j['boxscore']['teams'] ?? null;
        if (!is_array($teams) || count($teams) < 2) return [];

        $homeId = self::homeTeamId($j);
        $homeData = $awayData = [];
        foreach ($teams as $t) {
            $isHome = ((string)($t['team']['id'] ?? '')) === $homeId;
            foreach (($t['statistics'] ?? []) as $s) {
                $name = (string)($s['name'] ?? '');
                $val  = (string)($s['displayValue'] ?? '');
                if ($name === '') continue;
                if ($isHome) $homeData[$name] = $val;
                else         $awayData[$name] = $val;
            }
        }
        if (!$homeData && !$awayData) return [];

        // أسماء ESPN → التسمية المعروضة (نفس كتالوج LiveService)
        $catalog = [
            ['possessionPct',  'الاستحواذ',          'Possession',       '%'],
            ['totalShots',     'إجمالي التسديدات',   'Shots',            ''],
            ['shotsOnTarget',  'تسديدات على المرمى', 'Shots on target',  ''],
            ['wonCorners',     'ركلات ركنية',        'Corners',          ''],
            ['offsides',       'تسلّل',               'Offsides',         ''],
            ['foulsCommitted', 'الأخطاء',            'Fouls',            ''],
            ['yellowCards',    'بطاقات صفراء',       'Yellow cards',     ''],
            ['redCards',       'بطاقات حمراء',       'Red cards',        ''],
            ['saves',          'تصدّيات الحارس',      'Saves',           ''],
        ];
        $num = function ($v): ?int {
            $v = trim((string)$v);
            if ($v === '') return null;
            return (int)round((float)preg_replace('/[^0-9.\-]/', '', $v));
        };
        $out = [];
        foreach ($catalog as [$key, $kar, $ken, $unit]) {
            $vh = $num($homeData[$key] ?? null);
            $va = $num($awayData[$key] ?? null);
            if ($vh === null && $va === null) continue;
            if ((int)$vh === 0 && (int)$va === 0 && $key !== 'possessionPct') continue;
            $out[] = ['k' => $kar, 'k_en' => $ken, 'v' => [(int)$vh, (int)$va], 'unit' => $unit];
        }
        return $out;
    }

    /**
     * lineupFor($eventId) — التشكيلة الرسمية من ESPN rosters.
     * يعيد ['home'=>[formation,coach,start,subs], 'away'=>...] أو null إن لم تصدر.
     * ترتيب الأساسيين حسب formationPlace (حارس→دفاع→وسط→هجوم) ليتوافق مع رسم الملعب.
     */
    public static function lineupFor(string $eventId): ?array
    {
        $j = self::summary($eventId);
        if (!is_array($j)) return null;
        $rosters = $j['rosters'] ?? null;
        if (!is_array($rosters)) return null;

        $out = ['home' => null, 'away' => null];
        foreach ($rosters as $r) {
            $side = (($r['homeAway'] ?? '') === 'home') ? 'home' : ((($r['homeAway'] ?? '') === 'away') ? 'away' : null);
            if ($side === null) continue;
            $start = $subs = [];
            foreach (($r['roster'] ?? []) as $p) {
                $name = trim((string)($p['athlete']['displayName'] ?? ''));
                if ($name === '') continue;
                $numJ = isset($p['jersey']) ? (int)$p['jersey'] : null;
                if (!empty($p['starter'])) {
                    $start[] = ['name' => $name, 'number' => $numJ, 'grid' => '',
                                '_fp' => (int)($p['formationPlace'] ?? 99)];
                } else {
                    $subs[] = ['name' => $name, 'number' => $numJ];
                }
            }
            if (count($start) < 11) continue;   // التشكيلة لم تصدر بعد
            usort($start, fn($a, $b) => $a['_fp'] <=> $b['_fp']);
            foreach ($start as &$s) unset($s['_fp']);
            unset($s);
            $out[$side] = [
                'formation' => (string)($r['formation'] ?? ''),
                'coach'     => '',
                'start'     => $start,
                'subs'      => $subs,
            ];
        }
        return ($out['home'] && $out['away']) ? $out : null;
    }
}
