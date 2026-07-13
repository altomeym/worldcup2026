<?php
/**
 * featured.php — بطولات مميزة (نسخ تاريخية بسياق foot-boll).
 */
require __DIR__ . '/includes/bootstrap.php';

$ar = (current_lang() === 'ar');
$L  = fn(string $a, string $e) => $ar ? $a : $e;
$cards = FeaturedCups::cards();

$page_title = $L('بطولات مميزة', 'Featured tournaments');
$page_desc  = $L(
    'أبرز نسخ كأس العالم في التاريخ — سياق تحريري من foot-boll وروابط لأرشيف 2018 و2022 والسجل الكامل للأبطال.',
    'Legendary FIFA World Cup editions — foot-boll editorial context plus links to 2018/2022 archives and the full champions roll.'
);
$page_keywords = $L(
    'بطولات مميزة, كأس العالم, أرشيف, foot-boll',
    'featured World Cups, archive, foot-boll, history'
);

tpl('header');
?>

<div class="page-head">
  <h1>👑 <?= e($page_title) ?></h1>
  <p class="muted"><?= e($L(
    'نسخ لا تُنسى من تاريخ المونديال — مع لمحة foot-boll وروابط للنتائج حيث تتوفر.',
    'Unforgettable World Cup editions — with a foot-boll angle and results where available.'
  )) ?></p>
</div>

<div class="fc-grid">
  <?php foreach ($cards as $c): ?>
    <article class="fc-card<?= !empty($c['current']) ? ' fc-card-current' : '' ?>">
      <div class="fc-card-top">
        <span class="fc-year"><?= (int)$c['year'] ?></span>
        <span class="fc-tag"><?= e($c['tag']) ?></span>
      </div>
      <?php if (!empty($c['flag'])): ?>
        <div class="fc-winner">
          <?= flag_img_iso($c['flag'], 'w40') ?>
          <span><?= e($c['winner']) ?></span>
          <?php if ($c['score'] !== ''): ?><em><?= e($c['score']) ?></em><?php endif; ?>
        </div>
      <?php else: ?>
        <p class="fc-winner-text"><?= e($c['winner']) ?></p>
      <?php endif; ?>
      <?php if ($c['host'] !== ''): ?>
        <p class="fc-host muted">📍 <?= e($c['host']) ?></p>
      <?php endif; ?>
      <p class="fc-hook"><?= e($c['hook']) ?></p>
      <a class="fb-block-link" href="<?= e($c['url']) ?>">
        <?= e($L('استكشف ←', 'Explore →')) ?>
      </a>
    </article>
  <?php endforeach; ?>
</div>

<p class="muted" style="margin-top:24px;text-align:center">
  <a class="btn btn-sm" href="<?= e(url('archive.php')) ?>">📚 <?= e(t('archive')) ?></a>
</p>

<?php tpl('footer'); ?>
