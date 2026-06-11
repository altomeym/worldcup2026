<?php
/**
 * pitch.php — «الملعب التكتيكي» (Tactical Board / All-22).
 * يرسم الفريقين متقابلين على ملعب واحد بمواقع اللاعبين الحقيقية (grid)،
 * ويُتيح التفاعل (الضغط على لاعب لعرض اسمه ورقمه).
 *
 * يُستدعى عبر: render_tactical_pitch($lineup, $team1En, $team2En, ['sample'=>bool]).
 * مصدر البيانات: LiveService::lineupForMatch() — كل لاعب فيه grid = "row:col".
 */
if (!defined('WC2026')) { exit('Access denied'); }

/** هل لكل لاعبي القائمة موضع شبكي (row:col)؟ */
function pitch_has_grid(array $start): bool {
    if (!$start) return false;
    foreach ($start as $p) {
        if (($p['grid'] ?? '') === '') return false;
    }
    return true;
}

/** آخر كلمة من الاسم (لقب اللاعب) لعرضها تحت النقطة */
function pitch_last_name(string $n): string {
    $a = preg_split('/\s+/', trim($n));
    return ($a && end($a) !== false) ? (end($a) ?: $n) : $n;
}

/**
 * يبني صفوف اللاعبين من grid="row:col".
 * يرجّع ['rows'=>[r => [['p'=>player,'c'=>col], ...]], 'maxRow'=>int] أو null.
 */
function pitch_rows(array $start): ?array {
    if (!pitch_has_grid($start)) return null;
    $rows = [];
    foreach ($start as $p) {
        $g = array_map('intval', explode(':', (string)$p['grid']));
        $r = $g[0] ?? 0;
        $rows[$r][] = ['p' => $p, 'c' => $g[1] ?? 0];
    }
    ksort($rows);
    foreach ($rows as &$ln) { usort($ln, fn($a, $b) => $a['c'] <=> $b['c']); }
    unset($ln);
    return ['rows' => $rows, 'maxRow' => max(array_keys($rows))];
}

/**
 * تشكيلة نموذجية (للعرض قبل إعلان التشكيلات الرسمية).
 * أسماء = اختصارات مراكز (GK/CB/...) — لا أسماء لاعبين وهمية.
 */
function pitch_sample_lineup(): array {
    $mk = function (array $defs): array {     // 'row:col' => [number, posLabel]
        $out = [];
        foreach ($defs as $grid => $d) {
            $out[] = ['name' => $d[1], 'number' => $d[0], 'grid' => $grid];
        }
        return $out;
    };
    return [
        'team1' => ['formation' => '4-3-3', 'coach' => '', 'subs' => [], 'start' => $mk([
            '1:1' => [1, 'GK'],
            '2:1' => [2, 'RB'], '2:2' => [5, 'CB'], '2:3' => [4, 'CB'], '2:4' => [3, 'LB'],
            '3:1' => [6, 'CM'], '3:2' => [8, 'CM'], '3:3' => [10, 'AM'],
            '4:1' => [7, 'RW'], '4:2' => [9, 'ST'], '4:3' => [11, 'LW'],
        ])],
        'team2' => ['formation' => '4-4-2', 'coach' => '', 'subs' => [], 'start' => $mk([
            '1:1' => [1, 'GK'],
            '2:1' => [2, 'RB'], '2:2' => [5, 'CB'], '2:3' => [4, 'CB'], '2:4' => [3, 'LB'],
            '3:1' => [7, 'RM'], '3:2' => [6, 'CM'], '3:3' => [8, 'CM'], '3:4' => [11, 'LM'],
            '4:1' => [9, 'ST'], '4:2' => [10, 'ST'],
        ])],
    ];
}

/** يطبع أزرار لاعبي فريق على نصف الملعب. $side: 'home' (أسفل) أو 'away' (أعلى). */
function pitch_render_team(array $rowsData, string $teamLabel, string $side): void {
    $isHome = ($side === 'home');
    foreach ($rowsData['rows'] as $r => $ln) {
        $cnt = count($ln);
        $maxRow = $rowsData['maxRow'];
        // home: الحارس أسفل (96%) والهجوم نحو المنتصف (54%). away: معكوس (4% → 46%).
        if ($maxRow > 1) {
            $frac = ($r - 1) / ($maxRow - 1);
            $y = $isHome ? (96 - $frac * 42) : (4 + $frac * 42);
        } else {
            $y = $isHome ? 75 : 25;
        }
        foreach ($ln as $i => $cell) {
            // away: نعكس الأعمدة أفقياً ليتقابل الفريقان كما على التلفاز
            $x = $isHome ? (($i + 1) / ($cnt + 1) * 100)
                         : (100 - ($i + 1) / ($cnt + 1) * 100);
            $p   = $cell['p'];
            $num = ($p['number'] !== null && $p['number'] !== '') ? (int)$p['number'] : '';
            ?>
            <button type="button" class="pp pp-<?= e($side) ?>"
                    style="left:<?= round($x, 1) ?>%;top:<?= round($y, 1) ?>%"
                    data-name="<?= e($p['name']) ?>" data-num="<?= e((string)$num) ?>"
                    data-team="<?= e($teamLabel) ?>">
              <span class="pp-dot"><?= e((string)$num) ?></span>
              <span class="pp-name"><?= e(pitch_last_name($p['name'])) ?></span>
            </button>
            <?php
        }
    }
}

/**
 * يرسم الملعب التكتيكي الكامل (الفريقان متقابلان).
 * يرجّع true إن رُسم، false إن تعذّر (غياب المواقع الشبكية لأحد الفريقين).
 */
function render_tactical_pitch(array $lineup, string $t1en, string $t2en, array $opts = []): bool {
    $sample = !empty($opts['sample']);
    $L1 = $lineup['team1'] ?? null;
    $L2 = $lineup['team2'] ?? null;
    $R1 = $L1 ? pitch_rows($L1['start'] ?? []) : null;
    $R2 = $L2 ? pitch_rows($L2['start'] ?? []) : null;
    if (!$R1 || !$R2) { return false; }

    $f1 = !empty($L1['formation']) ? $L1['formation'] : '';
    $f2 = !empty($L2['formation']) ? $L2['formation'] : '';
    ?>
    <div class="tboard<?= $sample ? ' tboard-sample' : '' ?>">
      <?php
      // وسم أعلى اللوحة: «تجريبية» للنموذج، أو وسم مخصّص (مثل «تشكيلة متوقعة»)
      $boardTag = $sample ? ('🧪 ' . t('lineup_preview_tag')) : trim((string)($opts['tag'] ?? ''));
      ?>
      <?php if ($boardTag !== ''): ?>
        <span class="tboard-tag"><?= e($boardTag) ?></span>
      <?php endif; ?>

      <div class="tboard-head">
        <span class="tboard-team">
          <?= flag_img($t1en, 'w40') ?> <span class="tt-name"><?= e(team_name($t1en)) ?></span>
          <?php if ($f1 !== ''): ?><b class="tt-form"><?= e($f1) ?></b><?php endif; ?>
        </span>
        <span class="tboard-vs"><?= e(t('vs')) ?></span>
        <span class="tboard-team tboard-team-away">
          <?php if ($f2 !== ''): ?><b class="tt-form"><?= e($f2) ?></b><?php endif; ?>
          <span class="tt-name"><?= e(team_name($t2en)) ?></span> <?= flag_img($t2en, 'w40') ?>
        </span>
      </div>

      <div class="pitch-full" role="img"
           aria-label="<?= e(team_name($t1en) . ' ' . t('vs') . ' ' . team_name($t2en)) ?>">
        <span class="pm pm-mid"></span>
        <span class="pm pm-circle"></span>
        <span class="pm pm-spot"></span>
        <span class="pm pm-box pm-box-top"></span>
        <span class="pm pm-box pm-box-bot"></span>
        <span class="pm pm-six pm-six-top"></span>
        <span class="pm pm-six pm-six-bot"></span>

        <?php
        pitch_render_team($R1, team_name($t1en), 'home');
        pitch_render_team($R2, team_name($t2en), 'away');
        ?>
      </div>

      <p class="pitch-info" id="pitchInfo"><?= e(t('pitch_tap_hint')) ?></p>
    </div>
    <?php
    return true;
}
