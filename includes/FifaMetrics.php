<?php
/**
 * FifaMetrics.php — محرّك المقاييس الفنّيّة للاعبين (هجوم/إبداع/تمرير/دفاع/حراسة…).
 * يقرأ لقطة assets/fifa-metrics.json (مجاميع لكل لاعب) ويحسب وقت العرض:
 *   per-90 · المئويّات مقابل المركز · نقاط الفئة (0-100) · محاور الرادار.
 * المصدر: خلاصة FIFA المنظّمة (لقطة دائمة). البناء: tools/fifa-metrics-build.php.
 */
if (!defined('WC2026')) { exit('Access denied'); }

class FifaMetrics
{
    private const FILE = __DIR__ . '/../assets/fifa-metrics.json';

    /** الفئات بترتيب العرض. */
    public const CATS = [
        'att' => ['icon' => '⚽',  'ar' => 'الهجوم',          'en' => 'Attacking',       'fr' => 'Attaque'],
        'cre' => ['icon' => '🎨', 'ar' => 'الإبداع',          'en' => 'Creation',        'fr' => 'Création'],
        'lin' => ['icon' => '✂️', 'ar' => 'اختراق الخطوط',    'en' => 'Line-breaking',   'fr' => 'Ruptures de ligne'],
        'pas' => ['icon' => '🎯', 'ar' => 'خيارات التمرير',   'en' => 'Passing options', 'fr' => 'Options de passe'],
        'def' => ['icon' => '🛡️', 'ar' => 'الدفاع والضغط',    'en' => 'Defending & pressing', 'fr' => 'Défense & pressing'],
        'set' => ['icon' => '⛳', 'ar' => 'الكرات الثابتة',   'en' => 'Set pieces',      'fr' => 'Coups de pied arrêtés'],
        'dis' => ['icon' => '🟨', 'ar' => 'الانضباط',         'en' => 'Discipline',      'fr' => 'Discipline'],
        'gk'  => ['icon' => '🧤', 'ar' => 'حراسة المرمى',     'en' => 'Goalkeeping',     'fr' => 'Gardien'],
    ];

    /** key => [cat, ar, en, type(count|dec|pct), per90, inv]. الافتراضي count/per90=true. */
    public const M = [
        // الهجوم
        'Goals'                        => ['att', 'أهداف', 'Goals'],
        'Assists'                      => ['att', 'صناعة أهداف', 'Assists'],
        'Threat'                       => ['att', 'الخطورة', 'Threat', 'dec'],
        'NumberOfShotEndingSequences'  => ['att', 'تسلسلات تنتهي بتسديد', 'Shot-ending sequences'],
        'HeadedAttemptAtGoal'          => ['att', 'محاولات رأسيّة', 'Headed attempts'],
        'Crosses'                      => ['att', 'عرضيّات', 'Crosses'],
        'CrossesCompleted'             => ['att', 'عرضيّات مكتملة', 'Crosses completed'],
        // الإبداع
        'Passes'                       => ['cre', 'تمريرات', 'Passes'],
        'PassesCompleted'              => ['cre', 'تمريرات مكتملة', 'Passes completed'],
        'CompletedBallProgressions'    => ['cre', 'تقدّم بالكرة', 'Ball progressions'],
        'CompletedSwitchesOfPlay'      => ['cre', 'تحويلات اللعب', 'Switches of play'],
        'DistributionsUnderPressure'   => ['cre', 'توزيع تحت الضغط', 'Distributions under pressure'],
        'DistributionsCompletedUnderPressure' => ['cre', 'توزيع مكتمل تحت الضغط', 'Distributions completed under pressure'],
        'NumberOfInvolvements'         => ['cre', 'المشاركات', 'Involvements'],
        'NumberOfPossessionSequences'  => ['cre', 'تسلسلات الاستحواذ', 'Possession sequences'],
        // اختراق الخطوط
        'LinebreaksAttempted'             => ['lin', 'اختراقات (محاولة)', 'Linebreaks attempted'],
        'LinebreaksAttemptedCompleted'    => ['lin', 'اختراقات مكتملة', 'Linebreaks completed'],
        'LinebreaksAttemptedUnderPressure'=> ['lin', 'اختراقات تحت الضغط', 'Linebreaks under pressure'],
        'LinebreaksAttemptedAttackingLine'=> ['lin', 'اختراق خطّ الهجوم', 'Attacking-line breaks'],
        'LinebreaksAttemptedMidfieldLine' => ['lin', 'اختراق خطّ الوسط', 'Midfield-line breaks'],
        'LinebreaksAttemptedDefensiveLine'=> ['lin', 'اختراق خطّ الدفاع', 'Defensive-line breaks'],
        // خيارات التمرير
        'OffersToReceiveTotal'     => ['pas', 'عروض الاستلام', 'Offers to receive'],
        'ReceivedOffersToReceive'  => ['pas', 'عروض مُستجابة', 'Offers received'],
        'OffersToReceiveInBehind'  => ['pas', 'عروض خلف الدفاع', 'Offers in behind'],
        'OffersToReceiveInBetween' => ['pas', 'عروض بين الخطوط', 'Offers in between'],
        'OffersToReceiveInside'    => ['pas', 'عروض من الداخل', 'Offers inside'],
        'OffersToReceiveOutside'   => ['pas', 'عروض من الخارج', 'Offers outside'],
        // الدفاع والضغط
        'ForcedTurnovers'                 => ['def', 'استخلاصات مفروضة', 'Forced turnovers'],
        'DefensivePressuresApplied'       => ['def', 'ضغطات دفاعيّة', 'Defensive pressures'],
        'DirectDefensivePressuresApplied' => ['def', 'ضغطات مباشرة', 'Direct defensive pressures'],
        // الكرات الثابتة
        'Corners'         => ['set', 'ركلات ركنيّة', 'Corners'],
        'FreeKicks'       => ['set', 'ركلات حرّة', 'Free kicks'],
        'ThrowIns'        => ['set', 'رميات تماس', 'Throw-ins'],
        // الانضباط (أقلّ = أفضل)
        'YellowCards'  => ['dis', 'بطاقات صفراء', 'Yellow cards', 'count', true, true],
        'RedCards'     => ['dis', 'بطاقات حمراء', 'Red cards', 'count', true, true],
        'FoulsAgainst' => ['dis', 'أخطاء مرتكبة', 'Fouls committed', 'count', true, true],
        'FoulsFor'     => ['dis', 'أخطاء عليه', 'Fouls won'],
        'Offsides'     => ['dis', 'تسلّلات', 'Offsides', 'count', true, true],
        // حراسة المرمى
        'GoalkeeperSaves'          => ['gk', 'تصدّيات', 'Saves'],
        'GoalkeeperSavesOnTarget'  => ['gk', 'تصدّيات على المرمى', 'Saves on target'],
        'GoalsConceded'            => ['gk', 'أهداف مستقبَلة', 'Goals conceded', 'count', true, true],
        'GoalkeeperDefensiveActionsOutsidePenaltyArea' => ['gk', 'تدخّلات خارج المنطقة', 'Sweeper actions'],
    ];

    /** المحاور الرئيسة لرادار كلّ فئة (مفاتيح مختارة، بترتيب). */
    private const RADAR = [
        'att' => ['Assists', 'Crosses', 'NumberOfShotEndingSequences', 'Threat', 'Goals'],
        'cre' => ['CompletedSwitchesOfPlay', 'DistributionsUnderPressure', 'NumberOfPossessionSequences', 'CompletedBallProgressions', 'Passes', 'NumberOfInvolvements'],
        'lin' => ['LinebreaksAttemptedAttackingLine', 'LinebreaksAttemptedDefensiveLine', 'LinebreaksAttemptedCompleted', 'LinebreaksAttemptedUnderPressure', 'LinebreaksAttempted', 'LinebreaksAttemptedMidfieldLine'],
        'pas' => ['ReceivedOffersToReceive', 'OffersToReceiveInside', 'OffersToReceiveOutside', 'OffersToReceiveInBetween', 'OffersToReceiveInBehind', 'OffersToReceiveTotal'],
        'def' => ['ForcedTurnovers', 'DefensivePressuresApplied', 'DirectDefensivePressuresApplied'],
        'gk'  => ['GoalkeeperSaves', 'GoalkeeperSavesOnTarget', 'GoalkeeperDefensiveActionsOutsidePenaltyArea', 'GoalsConceded'],
    ];

    /** الفئات الكبرى لرادار الرأس (لاعب ميدان / حارس). */
    private const MACRO_FIELD = ['att', 'cre', 'lin', 'def'];
    private const MACRO_GK    = ['gk', 'cre', 'pas', 'def'];

    private static ?array $data = null;
    private static array $cohort = [];

    public static function load(): array
    {
        if (self::$data === null) {
            $d = is_file(self::FILE) ? json_decode((string)@file_get_contents(self::FILE), true) : null;
            self::$data = (is_array($d) && isset($d['players'])) ? $d : ['players' => []];
        }
        return self::$data;
    }

    public static function generated(): string { return (string)(self::load()['_generated'] ?? ''); }

    private static ?array $motm = null;
    /** خريطة رجل المباراة: «iso1|iso2» (مرتّب) → {name, team(code), rating, pid, photo, …}. */
    public static function loadMotm(): array
    {
        if (self::$motm === null) {
            $f = __DIR__ . '/../assets/fifa-motm.json';
            $d = is_file($f) ? json_decode((string)@file_get_contents($f), true) : null;
            self::$motm = (is_array($d) && isset($d['motm']) && is_array($d['motm'])) ? $d['motm'] : [];
        }
        return self::$motm;
    }

    /** رجل المباراة لمباراة بين فريقين (بالاسم الإنجليزي)، أو null. */
    public static function motmFor(string $t1En, string $t2En): ?array
    {
        if (!function_exists('team_flag')) return null;
        $a = strtolower(team_flag($t1En)); $b = strtolower(team_flag($t2En));
        if ($a === '' || $b === '') return null;
        $p = [$a, $b]; sort($p);
        return self::loadMotm()[implode('|', $p)] ?? null;
    }

    /** الاسم الإنجليزي لفريق رجل المباراة (أيّ الفريقَين هو عليه). */
    public static function motmTeamEn(array $rec, string $t1En, string $t2En): string
    {
        if (!function_exists('fifa_iso') || !function_exists('team_flag')) return $t1En;
        $iso = strtolower(fifa_iso((string)($rec['team'] ?? '')));
        return ($iso !== '' && $iso === strtolower(team_flag($t1En))) ? $t1En : $t2En;
    }

    /** سجلّ لاعب بالمعرّف. */
    public static function player(string $pid): ?array
    {
        return self::load()['players'][$pid] ?? null;
    }

    /** أوجِد معرّف لاعب بالاسم المُطبَّع (+الفريق اختياري). */
    public static function findId(string $name, string $teamEn = ''): ?string
    {
        $want = self::norm($name);
        if ($want === '') return null;
        $teamWant = $teamEn !== '' && function_exists('team_flag') ? strtolower(team_flag($teamEn)) : '';
        $fallback = null;
        foreach (self::load()['players'] as $pid => $pl) {
            if (self::norm((string)$pl['name']) !== $want) continue;
            if ($teamWant === '') return (string)$pid;
            // طابِق الفريق إن أمكن (رمز FIFA→iso)
            $pTeamIso = function_exists('fifa_iso') ? strtolower(fifa_iso((string)$pl['team'])) : '';
            if ($pTeamIso !== '' && $pTeamIso === $teamWant) return (string)$pid;
            $fallback = (string)$pid;
        }
        return $fallback;
    }

    /** القيمة الخام المجمّعة. */
    public static function raw(array $pl, string $key): float { return (float)($pl['m'][$key] ?? 0); }

    /** القيمة لكلّ 90 دقيقة (أو الخام لغير القابل للتطبيع). */
    public static function per90(array $pl, string $key): float
    {
        $raw = self::raw($pl, $key);
        $meta = self::M[$key] ?? null;
        $per90 = $meta ? ($meta[4] ?? true) : true;
        if (!$per90) return $raw;
        $min = max(1, (int)($pl['min'] ?? 90));
        return $raw * 90 / $min;
    }

    /** قيمة معروضة منسّقة. */
    public static function show(array $pl, string $key): string
    {
        $meta = self::M[$key] ?? null;
        $type = $meta[3] ?? 'count';
        $v = self::per90($pl, $key);
        if ($type === 'dec') return number_format($v, 2);
        if ($type === 'pct') return round($v) . '%';
        return $v >= 10 ? (string)round($v) : number_format($v, 1);
    }

    /** المئويّة (0-100) مقابل لاعبي نفس المركز. */
    public static function pct(array $pl, string $key): int
    {
        $grp = (string)($pl['grp'] ?? 'MID');
        $v = self::per90($pl, $key);
        $vals = self::cohort($key, $grp);
        $n = count($vals);
        if ($n < 3) return 50;
        $less = 0; foreach ($vals as $x) { if ($x < $v) $less++; }
        $p = (int)round($less / ($n - 1) * 100);
        $meta = self::M[$key] ?? null;
        if ($meta && !empty($meta[5])) $p = 100 - $p;   // inv: أقلّ = أفضل
        return max(0, min(100, $p));
    }

    /** قيم per-90 لكلّ لاعبي مجموعة المركز (مرتّبة، مخزّنة). */
    private static function cohort(string $key, string $grp): array
    {
        $ck = $key . '|' . $grp;
        if (isset(self::$cohort[$ck])) return self::$cohort[$ck];
        $vals = [];
        foreach (self::load()['players'] as $pl) {
            if ((string)($pl['grp'] ?? '') !== $grp) continue;
            $vals[] = self::per90($pl, $key);
        }
        sort($vals);
        return self::$cohort[$ck] = $vals;
    }

    /** نقطة الفئة (0-100) = متوسّط مئويّات محاورها. */
    public static function catScore(array $pl, string $catId): int
    {
        $keys = self::RADAR[$catId] ?? [];
        if (!$keys) return 0;
        $sum = 0; $c = 0;
        foreach ($keys as $k) { $sum += self::pct($pl, $k); $c++; }
        return $c ? (int)round($sum / $c) : 0;
    }

    /** محاور رادار الفئة: [[key, label, value, pct], …]. */
    public static function radar(array $pl, string $catId, string $lang = 'ar'): array
    {
        $out = [];
        foreach ((self::RADAR[$catId] ?? []) as $k) {
            $meta = self::M[$k] ?? null;
            if (!$meta) continue;
            $out[] = [
                'key'   => $k,
                'label' => ($lang === 'ar') ? $meta[1] : $meta[2],
                'val'   => self::show($pl, $k),
                'pct'   => self::pct($pl, $k),
            ];
        }
        return $out;
    }

    /** رادار الرأس الكبير: [[catId, label, score], …]. */
    public static function macro(array $pl, string $lang = 'ar'): array
    {
        $cats = (($pl['grp'] ?? '') === 'GK') ? self::MACRO_GK : self::MACRO_FIELD;
        $out = [];
        foreach ($cats as $cid) {
            $out[] = ['cat' => $cid, 'label' => self::CATS[$cid][$lang] ?? self::CATS[$cid]['en'], 'score' => self::catScore($pl, $cid)];
        }
        return $out;
    }

    /** الفئات التي يملك اللاعب فيها بيانات (لعرض الأقسام). */
    public static function activeCats(array $pl): array
    {
        $isGk = (($pl['grp'] ?? '') === 'GK');
        $order = $isGk ? ['gk', 'cre', 'pas', 'def', 'set', 'dis'] : ['att', 'cre', 'lin', 'pas', 'def', 'set', 'dis'];
        $out = [];
        foreach ($order as $cid) {
            foreach (self::RADAR[$cid] ?? [] as $k) {
                if (self::raw($pl, $k) > 0) { $out[] = $cid; break; }
            }
        }
        return $out;
    }

    private static function norm(string $s): string
    {
        $n = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s);
        if ($n === false) $n = $s;
        return preg_replace('/\s+/', ' ', trim(preg_replace('/[^A-Z0-9 ]/', ' ', strtoupper($n))));
    }
}
