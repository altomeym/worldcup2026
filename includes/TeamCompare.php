<?php
/**
 * TeamCompare.php — إحصائيات منتخب ومقارنة بين منتخبين في البطولة.
 */
if (!defined('WC2026')) { exit('Access denied'); }

class TeamCompare
{
    /** سجل المنتخب في البطولة من المباريات المنتهية. */
    public static function record(string $team): array
    {
        $team = trim($team);
        $out = ['p' => 0, 'w' => 0, 'd' => 0, 'l' => 0, 'gf' => 0, 'ga' => 0, 'gd' => 0, 'pts' => 0];
        foreach (DataService::matchesForTeam($team) as $m) {
            if (($m['_status'] ?? '') !== 'finished') continue;
            if (!isset($m['score']['ft']) || !is_array($m['score']['ft'])) continue;
            $t1 = trim($m['team1'] ?? '');
            $t2 = trim($m['team2'] ?? '');
            if ($t1 !== $team && $t2 !== $team) continue;
            $g1 = (int)$m['score']['ft'][0];
            $g2 = (int)$m['score']['ft'][1];
            $gf = ($t1 === $team) ? $g1 : $g2;
            $ga = ($t1 === $team) ? $g2 : $g1;
            $out['p']++;
            $out['gf'] += $gf;
            $out['ga'] += $ga;
            if ($gf > $ga) { $out['w']++; $out['pts'] += 3; }
            elseif ($gf < $ga) { $out['l']++; }
            else { $out['d']++; $out['pts'] += 1; }
        }
        $out['gd'] = $out['gf'] - $out['ga'];
        return $out;
    }

    /** مواجهات مباشرة بين منتخبين في البطولة الحالية. */
    public static function headToHead(string $a, string $b): array
    {
        $a = trim($a); $b = trim($b);
        $out = [];
        foreach (DataService::allMatches() as $m) {
            if (($m['_status'] ?? '') !== 'finished') continue;
            $t1 = trim($m['team1'] ?? '');
            $t2 = trim($m['team2'] ?? '');
            if (($t1 === $a && $t2 === $b) || ($t1 === $b && $t2 === $a)) {
                $out[] = $m;
            }
        }
        return $out;
    }

    /** صف FIFA dashboard لمنتخب أو null. */
    public static function fifaRow(string $team): ?array
    {
        if (!class_exists('FifaStats')) return null;
        $dash = FifaStats::teamDashboard();
        foreach ($dash['teams'] ?? [] as $row) {
            if (($row['team'] ?? '') === $team) return $row;
        }
        return null;
    }

    /** ترتيب المنتخب في مجموعته (1-based) أو null. */
    public static function groupRank(string $team): ?int
    {
        $all = DataService::allTeams();
        $group = $all[$team] ?? '';
        if ($group === '') return null;
        foreach (Standings::forGroup($group) as $i => $r) {
            if (($r['team'] ?? '') === $team) return $i + 1;
        }
        return null;
    }
}
