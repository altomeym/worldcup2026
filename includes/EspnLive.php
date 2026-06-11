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
 * حدود المصدر: نتيجة + دقيقة + حالة فقط (لا إحصائيات/بطاقات/تشكيلات) —
 * وهذا يكفي لعرض النتيجة اللحظية على كل صفحات الموقع.
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
                'referee'    => null,
                '_src'       => 'espn',
            ];
        }
        return $out;
    }
}
