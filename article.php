<?php
/**
 * article.php — صفحة عرض الخبر داخل الموقع (?i=معرّف الخبر).
 * تعرض العنوان وصورة المصدر والوقت، وزرّاً بارزاً لقراءة المقال كاملاً على المصدر.
 */
require __DIR__ . '/includes/bootstrap.php';

$id   = isset($_GET['i']) ? trim($_GET['i']) : '';
$item = News::find($id);

if ($item === null) {
    $page_title = t('news');
    tpl('header');
    echo '<div class="alert">' . e(t('news_not_found')) . '</div>';
    echo '<p><a class="btn" href="' . e(url('news.php')) . '">‹ ' . e(t('back_to_news')) . '</a></p>';
    tpl('footer');
    exit;
}

// إثراء من صفحة المصدر المباشرة (صورة أوضح + نص تمهيدي)
$rich    = News::enrich($item['link']);
$heroImg = !empty($rich['image']) ? $rich['image'] : ($item['image'] ?? '');
$summary = (!empty($rich['desc']) && mb_strlen($rich['desc']) > mb_strlen($item['summary'] ?? ''))
         ? $rich['desc'] : ($item['summary'] ?? '');

$page_title = $item['title'];
$page_desc  = !empty($item['context'])
            ? mb_substr($item['context'], 0, 160)
            : ($summary !== '' ? mb_substr($summary, 0, 160) : ($item['title']));
$seo_type   = 'article';
$page_robots = !empty($item['context']) ? 'index,follow' : 'noindex,follow';
$relatedMatch = null;
if (!empty($item['match_index'])) {
    $relatedMatch = DataService::matchByIndex((int)$item['match_index']);
}
gtm_add([
    'article_id'     => $id,
    'article_title'  => $item['title'],
    'article_source' => (string)($item['source'] ?? ($item['host'] ?? '')),
    'content_group'  => 'news_article',
]);
tpl('header');
?>

<a class="back-link" href="<?= e(url('news.php')) ?>">‹ <?= e(t('back_to_news')) ?></a>

<article class="article-view">
  <?php if ($heroImg !== ''): ?>
    <div class="article-hero"><img src="<?= e($heroImg) ?>" alt="<?= e($item['title']) ?>" loading="lazy"></div>
  <?php endif; ?>

  <div class="article-head">
    <?php if (!empty($item['logo'])): ?>
      <span class="article-logo"><img src="<?= e($item['logo']) ?>" alt="<?= e($item['source'] ?? '') ?>" width="64" height="64"></span>
    <?php endif; ?>
    <div class="article-headmeta">
      <?php if (!empty($item['source'])): ?>
        <span class="article-source"><?= e($item['source']) ?></span>
      <?php endif; ?>
      <?php if (!empty($item['ts'])): ?>
        <span class="article-time"><?= e(t('published')) ?>: <?= local_dt((int)$item['ts'], 'datetime') ?></span>
      <?php endif; ?>
    </div>
  </div>

  <h1 class="article-title"><?= e($item['title']) ?></h1>

  <?php if (!empty($item['context'])): ?>
    <aside class="article-editorial">
      <span class="article-editorial-label"><?= e(t('news_editorial')) ?></span>
      <p><?= e($item['context']) ?></p>
      <?php if (!empty($item['teams'])): ?>
        <div class="article-editorial-links">
          <?php foreach (array_slice($item['teams'], 0, 3) as $tEn): ?>
            <?php if (isset(DataService::allTeams()[$tEn])): ?>
              <a class="chip-link" href="<?= e(url('team.php', ['team' => $tEn])) ?>"><?= e(team_name($tEn)) ?></a>
            <?php endif; ?>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </aside>
  <?php endif; ?>

  <?php if ($relatedMatch !== null): ?>
    <div class="article-related">
      <span class="article-related-label"><?= e(t('news_related_match')) ?></span>
      <a class="article-related-match" href="<?= e(url('match.php', ['id' => (int)$item['match_index']])) ?>">
        <span class="arm-teams"><?= e(team_name($relatedMatch['team1'] ?? '')) ?> × <?= e(team_name($relatedMatch['team2'] ?? '')) ?></span>
        <?php if (!empty($relatedMatch['score']['ft'])): ?>
          <span class="arm-score"><?= e(score_text($relatedMatch)) ?></span>
        <?php elseif ($ts = DataService::matchTimestamp($relatedMatch)): ?>
          <span class="arm-when"><?= local_dt($ts, 'date_short') ?></span>
        <?php endif; ?>
      </a>
    </div>
  <?php endif; ?>

  <?php if ($summary !== ''): ?>
    <p class="article-summary muted"><?= e($summary) ?></p>
  <?php endif; ?>

  <div class="article-cta">
    <a class="btn btn-accent" href="<?= e($item['link']) ?>" target="_blank" rel="noopener nofollow"
       data-gtm-event="article_read"
       data-gtm-article-id="<?= e($id) ?>"
       data-gtm-source="<?= e($item['source'] ?? ($item['host'] ?? '')) ?>">
      <?= e(t('read_full')) ?> <?= e($item['source'] ?: ($item['host'] ?? '')) ?> ↗
    </a>
  </div>

  <?php render_share(canonical_url(), $item['title']); ?>
</article>

<!-- أخبار أخرى -->
<?php $more = array_slice(array_filter(News::latest(7), fn($n) => ($n['id'] ?? '') !== $id), 0, 5); ?>
<?php if ($more): ?>
<section class="fb-block" style="margin-top:30px">
  <div class="fb-block-head">
    <h2><span class="fb-block-bar"></span><?= e(t('latest_news')) ?></h2>
    <a class="fb-block-link" href="<?= e(url('news.php')) ?>"><?= e(t('news_more')) ?> ›</a>
  </div>
  <div class="fb-feed fb-feed-grid fb-feed-grid--sm">
    <?php foreach ($more as $it) render_news_item($it, 'card'); ?>
  </div>
</section>
<?php endif; ?>

<?php tpl('footer'); ?>
