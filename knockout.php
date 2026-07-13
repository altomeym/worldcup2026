<?php
/**
 * knockout.php — شجرة الأدوار الإقصائية (Bracket).
 */
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/templates/match_card.php';

$page_title = t('knockout');
$ar = (current_lang() === 'ar');
$page_desc  = $ar
    ? 'شجرة الأدوار الإقصائية لكأس العالم 2026 على foot-boll — دور الـ32، ربع النهائي، نصف النهائي، والنهائي مع روابط لكل مباراة.'
    : 'World Cup 2026 knockout bracket on foot-boll — Round of 32, quarters, semis, and final with links to every match.';
$page_keywords = $ar
    ? 'أدوار إقصائية, شجرة المونديال, foot-boll, كأس العالم 2026'
    : 'knockout stage, World Cup bracket, foot-boll, 2026';
$stages     = Bracket::stages();

tpl('header');
?>

<div class="page-head">
  <h1>🏆 <?= e(t('knockout')) ?></h1>
</div>

<?php if (!$stages): ?>
  <p class="empty-note">
    <?= e(current_lang()==='ar'
        ? 'لم تبدأ الأدوار الإقصائية بعد — تابع مباريات المجموعات.'
        : 'Knockout stage has not started yet — follow the group matches.') ?>
  </p>
<?php else: ?>
  <?php foreach ($stages as $stageKey => $matches): ?>
    <section class="ko-stage" data-autorefresh="1">
      <h2 class="ko-stage-title">
        <span class="fb-block-bar"></span>
        <?= e(t($stageKey)) ?>
      </h2>
      <div class="fb-matches <?= $stageKey==='final' ? 'final-grid' : '' ?>">
        <?php foreach ($matches as $m) render_match_card($m); ?>
      </div>
    </section>
  <?php endforeach; ?>
<?php endif; ?>

<?php tpl('footer'); ?>
