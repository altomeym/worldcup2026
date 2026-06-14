<?php
/**
 * bookings.php — الإنذارات والطرد (البطاقات الصفراء/الحمراء على مستوى البطولة).
 *
 * يمسح كل المباريات ويجمع كل بطاقة في $m['cards'] (تأتي من API-Football أثناء
 * البطولة الفعلية؛ غائبة الآن). كل بطاقة:
 *   ['team' => 1|2, 'minute' => int, 'name' => string, 'type' => 'yellow'|'red']
 * تُعرض في جدولين منفصلين: الحمراء ثم الصفراء.
 */
require __DIR__ . '/includes/bootstrap.php';

// اجمع كل البطاقات من كل المباريات
$cards    = [];
$yellows  = 0;
$reds     = 0;

foreach (DataService::allMatches() as $m) {
    if (empty($m['cards']) || !is_array($m['cards'])) {
        continue;
    }
    foreach ($m['cards'] as $c) {
        if (!is_array($c)) continue;
        $type = ($c['type'] ?? '') === 'red' ? 'red' : 'yellow';
        if ($type === 'red') { $reds++; } else { $yellows++; }

        // المنتخب صاحب البطاقة (team = 1 أو 2 → team1/team2 لتلك المباراة)
        $teamSide = ((int)($c['team'] ?? 0) === 2) ? 2 : 1;
        $teamEn   = $teamSide === 2 ? ($m['team2'] ?? '') : ($m['team1'] ?? '');

        $cards[] = [
            'match_index' => (int)($m['_index'] ?? 0),
            'team1'       => $m['team1'] ?? '',
            'team2'       => $m['team2'] ?? '',
            'team_en'     => $teamEn,
            'minute'      => (int)($c['minute'] ?? 0),
            'name'        => (string)($c['name'] ?? ''),
            'type'        => $type,
        ];
    }
}

// ترتيب: حسب رقم المباراة ثم الدقيقة
usort($cards, function ($a, $b) {
    return [$a['match_index'], $a['minute']] <=> [$b['match_index'], $b['minute']];
});

// افصل البطاقات نوعين — جدول مستقل لكل نوع (أوضح)
$redCards    = array_values(array_filter($cards, fn($c) => $c['type'] === 'red'));
$yellowCards = array_values(array_filter($cards, fn($c) => $c['type'] === 'yellow'));

// عارض جدول بطاقات نوع واحد (DRY — يُستدعى للحمراء ثم الصفراء)
$renderCardTable = function (array $list): void {
    if (empty($list)) {
        echo '<p class="empty-note">' . e(t('no_cards')) . '</p>';
        return;
    }
    ?>
    <div class="lb-wrap">
      <table class="leaderboard">
        <thead>
          <tr>
            <th><?= e(t('minute')) ?></th>
            <th class="lb-name"><?= e(t('player')) ?></th>
            <th class="lb-name"><?= e(t('team')) ?></th>
            <th class="lb-name"><?= e(t('match')) ?></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($list as $c):
            $matchUrl = url('match.php', ['id' => $c['match_index']]);
          ?>
            <tr>
              <td class="lb-rank"><?= (int)$c['minute'] ?>'</td>
              <td class="lb-name"><?= $c['name'] !== '' ? e($c['name']) : '—' ?></td>
              <td class="lb-name">
                <?= flag_img($c['team_en'], 'w40') ?>
                <?= e(team_name($c['team_en'])) ?>
              </td>
              <td class="lb-name">
                <a class="section-link" href="<?= e($matchUrl) ?>">
                  <?= e(team_name($c['team1'])) ?> <?= e(t('vs')) ?> <?= e(team_name($c['team2'])) ?>
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php
};

// صورة المشاركة (OG) = بطاقة البطاقات بهويّة الموقع (مجاميع + الأكثر بطاقات)
if (!empty($cards)) {
    $page_image = url('card_img.php', ['mode' => 'cards', 'd' => card_rev()]);
}
$page_title = t('bookings');
$page_desc  = t('bookings_intro');
tpl('header');
?>

<div class="page-head">
  <h1>🟨 <?= e(t('bookings')) ?></h1>
  <p class="muted"><?= e(t('bookings_intro')) ?></p>
</div>

<div class="scoring-card">
  <span class="sc-pill sc-yellow">🟨 <?= e(t('yellow_cards')) ?>: <?= (int)$yellows ?></span>
  <span class="sc-pill sc-red">🟥 <?= e(t('red_cards')) ?>: <?= (int)$reds ?></span>
</div>

<?php if (empty($cards)): ?>
  <p class="empty-note"><?= e(t('no_cards')) ?></p>
<?php else: ?>
  <section class="md-section">
    <h2 class="section-head">🟥 <?= e(t('red_cards')) ?> (<?= (int)$reds ?>)</h2>
    <?php $renderCardTable($redCards); ?>
  </section>

  <section class="md-section">
    <h2 class="section-head">🟨 <?= e(t('yellow_cards')) ?> (<?= (int)$yellows ?>)</h2>
    <?php $renderCardTable($yellowCards); ?>
  </section>

  <!-- ============ مشاركة ============ -->
  <?php render_share(canonical_url(), t('bookings') . ' — ' . SITE_NAME_AR); ?>
<?php endif; ?>

<?php tpl('footer'); ?>
