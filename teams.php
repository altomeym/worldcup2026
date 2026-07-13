<?php
/**
 * teams.php — قائمة كل المنتخبات المشاركة مجمّعة حسب المجموعة.
 */
require __DIR__ . '/includes/bootstrap.php';

$ar = (current_lang() === 'ar');
$page_title = t('teams');
$page_desc  = $ar
    ? 'دليل المنتخبات الـ48 في كأس العالم 2026 — مجموعات، أعلام، وروابط لملفات كل منتخب على foot-boll.'
    : 'Guide to all 48 World Cup 2026 teams — groups, flags, and links to each national team profile on foot-boll.';
$page_keywords = $ar
    ? 'منتخبات 2026, كأس العالم, foot-boll, المجموعات'
    : 'World Cup 2026 teams, groups, foot-boll, national teams';

// المنتخبات مع مجموعاتها
$teams = DataService::allTeams();   // [team => group]

// تجميع حسب المجموعة
$byGroup = [];
foreach ($teams as $team => $group) {
    $byGroup[$group ?: '—'][] = $team;
}
ksort($byGroup);

tpl('header');
?>

<div class="page-head">
  <h1><?= e(t('all_teams')) ?></h1>
  <p class="muted"><?= count($teams) ?> <?= e(t('qualified')) ?></p>
</div>

<?php if (!$teams): ?>
  <p class="empty-note"><?= e(t('no_data')) ?></p>
<?php else: ?>
  <?php foreach ($byGroup as $group => $list): ?>
    <section class="teams-group">
      <h2 class="teams-group-title"><?= e(group_label($group)) ?></h2>
      <div class="teams-grid">
        <?php foreach ($list as $team): ?>
          <a class="team-chip" href="<?= e(url('team.php', ['team' => $team])) ?>">
            <?= flag_img($team, 'w160') ?>
            <span><?= e(team_name($team)) ?></span>
          </a>
        <?php endforeach; ?>
      </div>
    </section>
  <?php endforeach; ?>
<?php endif; ?>

<?php tpl('footer'); ?>
