<?php
/**
 * DailyOpinion.php — فقرة تحريرية يومية قصيرة (محتوى أصلي من معطيات المباراة).
 */
if (!defined('WC2026')) { exit('Access denied'); }

class DailyOpinion
{
    /** المباراة المميّزة لليوم: مباشر ← اليوم ← أقرب قادمة. */
    public static function featured(): ?array
    {
        $live = [];
        foreach (DataService::allMatches() as $m) {
            if (($m['_status'] ?? '') === 'live') $live[] = $m;
        }
        if ($live) {
            usort($live, fn($a, $b) =>
                (DataService::matchTimestamp($a) ?? 0) <=> (DataService::matchTimestamp($b) ?? 0));
            return $live[0];
        }

        $today = DataService::matchesOnDate();
        $upcoming = array_values(array_filter($today, fn($m) => ($m['_status'] ?? '') === 'upcoming'));
        if ($upcoming) {
            usort($upcoming, fn($a, $b) =>
                (DataService::matchTimestamp($a) ?? PHP_INT_MAX) <=> (DataService::matchTimestamp($b) ?? PHP_INT_MAX));
            return $upcoming[0];
        }

        $next = DataService::upcomingMatches(1);
        return $next[0] ?? null;
    }

    /** نص الرأي التحريري أو null إن لم تتوفر مباراة صالحة. */
    public static function text(?array $m, bool $ar): ?string
    {
        if ($m === null) return null;
        $t1 = trim($m['team1'] ?? '');
        $t2 = trim($m['team2'] ?? '');
        if (!is_real_team($t1) || !is_real_team($t2)) return null;

        $tn1 = team_name($t1);
        $tn2 = team_name($t2);
        $round = round_label($m['round'] ?? '');
        $status = $m['_status'] ?? 'upcoming';
        $r1 = Rankings::of($t1);
        $r2 = Rankings::of($t2);
        $id = (int)($m['_index'] ?? 0);
        $ts = DataService::matchTimestamp($m);
        $timeStr = $ts ? local_dt($ts, 'time') : '';

        $rankPhrase = '';
        if ($r1 && $r2) {
            $rankPhrase = $ar
                ? "الفريقان في المركزين #{$r1} و#{$r2} عالمياً."
                : "They sit at FIFA ranks #{$r1} and #{$r2}.";
        } elseif ($r1 || $r2) {
            $rk = $r1 ?: $r2;
            $rankPhrase = $ar
                ? "أحد الطرفين في المركز #{$rk} عالمياً."
                : "One side is ranked #{$rk} globally.";
        }

        $variant = (int)date('z') % 4;

        if ($status === 'live') {
            $score = '';
            if (isset($m['score']['ft']) && is_array($m['score']['ft'])) {
                $score = $ar
                    ? ' النتيجة الحالية ' . (int)$m['score']['ft'][0] . '-' . (int)$m['score']['ft'][1] . '.'
                    : ' Current score ' . (int)$m['score']['ft'][0] . '-' . (int)$m['score']['ft'][1] . '.';
            }
            return $ar
                ? "رأي foot-boll: اللقاء الأهم الآن هو {$tn1} × {$tn2} في {$round}.{$score} تابع الأحداث والإحصائيات لحظياً على صفحة المباراة."
                : "foot-boll pick: the match to watch right now is {$tn1} vs {$tn2} in {$round}.{$score} Follow live events and stats on the match page.";
        }

        if ($status === 'finished' && isset($m['score']['ft'])) {
            $g1 = (int)$m['score']['ft'][0];
            $g2 = (int)$m['score']['ft'][1];
            return $ar
                ? "رأي foot-boll: انتهى {$tn1} {$g1}-{$g2} {$tn2} في {$round}. راجع ملخّص المباراة، تقرير FIFA، وتصويت رجل المباراة على foot-boll."
                : "foot-boll take: {$tn1} {$g1}-{$g2} {$tn2} in {$round} is done. Catch the summary, FIFA report, and MOTM vote on foot-boll.";
        }

        $templatesAr = [
            "رأي foot-boll: مباراة اليوم الأهم هي {$tn1} و{$tn2} في {$round}" . ($timeStr ? " الساعة {$timeStr}" : '') . ". {$rankPhrase} جرّب التوقعات واقرأ معاينة الذكاء الاصطناعي قبل الصافرة.",
            "رأي foot-boll: نرصد اليوم لقاء {$tn1} × {$tn2} — {$round}. {$rankPhrase} قارن المنتخبين على صفحة المقارنة ثم شارك توقعك.",
            "رأي foot-boll: {$tn1} يواجه {$tn2} في {$round}" . ($timeStr ? " · {$timeStr}" : '') . ". {$rankPhrase} هذا اللقاء يستحق متابعة التحليل والإحصائيات على foot-boll.",
            "رأي foot-boll: تركيزنا اليوم على {$tn1} ضد {$tn2} ({$round}). {$rankPhrase} لا تفوّت التوقعات التفاعلية وتحليل ما قبل المباراة.",
        ];
        $templatesEn = [
            "foot-boll pick: today's headline fixture is {$tn1} vs {$tn2} in {$round}" . ($timeStr ? " at {$timeStr}" : '') . ". {$rankPhrase} Try predictions and read the AI preview before kickoff.",
            "foot-boll pick: we're watching {$tn1} × {$tn2} — {$round}. {$rankPhrase} Compare the teams on our compare page, then share your prediction.",
            "foot-boll pick: {$tn1} meet {$tn2} in {$round}" . ($timeStr ? " · {$timeStr}" : '') . ". {$rankPhrase} Worth following the analysis and stats on foot-boll.",
            "foot-boll pick: focus today on {$tn1} vs {$tn2} ({$round}). {$rankPhrase} Don't miss interactive predictions and the pre-match analysis.",
        ];

        $tpl = $ar ? $templatesAr[$variant] : $templatesEn[$variant];
        return trim(preg_replace('/\s{2,}/', ' ', str_replace('  ', ' ', $tpl)));
    }

    /** بيانات العرض: نص + مباراة + رابط. */
    public static function block(bool $ar): ?array
    {
        $m = self::featured();
        $text = self::text($m, $ar);
        if ($text === null || $m === null) return null;
        return [
            'text'  => $text,
            'match' => $m,
            'url'   => url('match.php', ['id' => (int)($m['_index'] ?? 0)]),
        ];
    }
}
