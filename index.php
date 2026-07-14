<?php

/**

 * index.php — الصفحة الرئيسية.

 */

require __DIR__ . '/includes/bootstrap.php';

require __DIR__ . '/templates/match_card.php';



$page_title = t('home');

$ar         = (current_lang() === 'ar');

$page_desc = $ar

  ? 'foot-boll: مركزك العربي لكأس العالم 2026 — جدول مباريات اليوم، نتائج مباشرة، توقعات تفاعلية، وإحصائيات FIFA التفصيلية.'

  : 'foot-boll: your Arabic World Cup 2026 hub — today\'s fixtures, live scores, predictions, and FIFA analytics.';

$page_keywords = $ar

  ? 'مونديال 2026, foot-boll, نتائج مباشرة, توقعات, إحصائيات FIFA'

  : 'World Cup 2026, foot-boll, live scores, predictions, FIFA stats';



$today      = DataService::matchesOnDate();

$upcoming   = DataService::upcomingMatches(6);

$results    = DataService::latestResults(6);

$finalM     = Bracket::finalMatch();

$dataOk     = DataService::isOk();

$logoUrl    = rtrim(SITE_URL, '/') . '/assets/img/logo.png';

$aiSpotlight = null;
if (AiContent::enabled()) {
    foreach (array_merge($today, $upcoming, $results) as $m) {
        $finished = isset($m['score']['ft']) && is_array($m['score']['ft']);
        $type = $finished ? 'summary' : 'preview';
        $text = AiContent::forMatch($m, $type);
        if ($text !== null) {
            $aiSpotlight = ['m' => $m, 'type' => $type, 'text' => $text];
            break;
        }
    }
}

$kickoff = null;

foreach (DataService::allMatches() as $m) {

    $ts = DataService::matchTimestamp($m);

    if ($ts !== null && ($kickoff === null || $ts < $kickoff)) $kickoff = $ts;

}



tpl('header');

?>



<!-- ============ البطل / Hero ============ -->

<?php

$champion = null;

if ($finalM && isset($finalM['score']['ft'])) {

    [$fg1, $fg2] = $finalM['score']['ft'];

    $champion = ($fg1 >= $fg2) ? ($finalM['team1'] ?? '') : ($finalM['team2'] ?? '');

}

?>

<?php if ($champion && is_real_team($champion)): ?>

<section class="fb-winner">

  <div class="fb-winner-glow"></div>

  <span class="fb-winner-trophy">🏆</span>

  <p class="fb-winner-label"><?= e(t('final_winner')) ?></p>

  <div class="fb-winner-name">

    <?= flag_img($champion, 'w160') ?>

    <h2><?= e(team_name($champion)) ?></h2>

  </div>

</section>

<?php else: ?>

<section class="fb-hero">

  <div class="fb-hero-bg"></div>

  <div class="fb-hero-body">

    <p class="fb-hero-eyebrow">FIFA WORLD CUP</p>

    <h1 class="fb-hero-heading">2026</h1>

    <div class="fb-hero-flags" aria-hidden="true">

      <img src="<?= e(flag_url_iso('ca', 'w80')) ?>" alt="" loading="eager" width="40" height="30">

      <img src="<?= e(flag_url_iso('mx', 'w80')) ?>" alt="" loading="eager" width="40" height="30">

      <img src="<?= e(flag_url_iso('us', 'w80')) ?>" alt="" loading="eager" width="40" height="30">

    </div>

    <p class="fb-hero-lead"><?= e(t('hero_tagline')) ?></p>

    <div class="fb-hero-actions">

      <a class="btn-cta" href="<?= e(url('predict.php')) ?>"><?= e(t('play_predict')) ?></a>

      <a class="btn-ghost" href="<?= e(url('matches.php')) ?>"><?= e(t('explore_matches')) ?> ›</a>

    </div>

    <?php if ($kickoff && time() < $kickoff): ?>

    <div class="countdown" id="countdown" data-target="<?= (int)$kickoff ?>">

      <p class="cd-label"><?= e(t('kickoff_in')) ?></p>

      <div class="cd-boxes">

        <div class="cd-box"><span class="cd-num" data-cd="d">—</span><span class="cd-lbl"><?= e(t('cd_days')) ?></span></div>

        <div class="cd-box"><span class="cd-num" data-cd="h">—</span><span class="cd-lbl"><?= e(t('cd_hours')) ?></span></div>

        <div class="cd-box"><span class="cd-num" data-cd="m">—</span><span class="cd-lbl"><?= e(t('cd_mins')) ?></span></div>

        <div class="cd-box"><span class="cd-num" data-cd="s">—</span><span class="cd-lbl"><?= e(t('cd_secs')) ?></span></div>

      </div>

    </div>

    <?php endif; ?>

    <div class="fb-hero-stats" aria-label="<?= e($ar ? 'أرقام كأس العالم 2026' : 'World Cup 2026 in numbers') ?>">

      <div class="fb-hero-stat"><span class="fb-hero-stat-n">48</span><span class="fb-hero-stat-l"><?= e(t('teams')) ?></span></div>

      <div class="fb-hero-stat"><span class="fb-hero-stat-n">104</span><span class="fb-hero-stat-l"><?= e(t('matches')) ?></span></div>

      <div class="fb-hero-stat"><span class="fb-hero-stat-n">16</span><span class="fb-hero-stat-l"><?= e(t('host_cities')) ?></span></div>

      <div class="fb-hero-stat"><span class="fb-hero-stat-n">3</span><span class="fb-hero-stat-l"><?= e($ar ? '3 دول مستضيفة' : 'Host nations') ?></span></div>

    </div>

  </div>

</section>

<?php endif; ?>



<?php if (!$dataOk): ?>

  <div class="alert"><?= e(t('no_data')) ?></div>

<?php endif; ?>



<!-- ============ الميزات (قبل مباريات اليوم) ============ -->

<section class="fb-hub">

  <div class="fb-hub-head">

    <h2><?= e(t('engage_title')) ?></h2>

    <p class="muted"><?= e(t('engage_sub')) ?></p>

  </div>

  <div class="fb-hub-grid">

    <?php

    $playFeatures = [

        ['predict.php',     '🎯', t('predict'),     t('f_predict')],

        ['bracket.php',     '🏆', t('bracket'),     t('f_bracket')],

        ['stickers.php',    '🃏', t('stickers'),    t('f_stickers')],

        ['trivia.php',      '❓', t('trivia'),      t('f_trivia')],

        ['leaderboard.php', '🏅', t('leaderboard'), t('f_leaderboard')],

    ];

    foreach ($playFeatures as [$page, $icon, $title, $desc]): ?>

      <a class="fb-hub-card" href="<?= e(url($page)) ?>">

        <span class="fb-hub-icon"><?= $icon ?></span>

        <span class="fb-hub-body">

          <span class="fb-hub-title"><?= e($title) ?></span>

          <span class="fb-hub-desc"><?= e($desc) ?></span>

        </span>

      </a>

    <?php endforeach; ?>

  </div>

</section>



<!-- ============ مركز التحليل المتقدم ============ -->

<section class="fb-analytics">

  <div class="fb-analytics-head">

    <h2><?= e(t('analytics_title')) ?></h2>

    <p class="muted"><?= e(t('analytics_sub')) ?></p>

  </div>

  <div class="fb-analytics-grid">

    <?php

    $analyticsFeatures = [

        ['dashboard.php', '📊', $ar ? 'لوحة الإحصائيات' : 'Stats dashboard', t('f_dashboard')],

        ['compare.php',   '⚖️', $ar ? 'مقارنة منتخبين' : 'Team comparison',   t('f_compare')],

        ['physical.php',  '🏃', $ar ? 'البيانات البدنية' : 'Physical data',   t('f_physical')],

        ['motm.php',      '🌟', $ar ? 'رجل المباراة' : 'Player of the Match', t('f_motm')],

        ['stats.php',     '📈', t('stats'),            t('f_stats')],

        ['topscorers.php','⚽', t('top_scorers'),       t('f_scorers')],

        ['referees.php',  '🧑‍⚖️', t('referees'),         t('f_referees')],

    ];

    foreach ($analyticsFeatures as [$page, $icon, $title, $desc]): ?>

      <a class="fb-analytics-card" href="<?= e(url($page)) ?>">

        <span class="fb-analytics-icon"><?= $icon ?></span>

        <span class="fb-analytics-body">

          <span class="fb-analytics-title"><?= e($title) ?></span>

          <span class="fb-analytics-desc"><?= e($desc) ?></span>

        </span>

      </a>

    <?php endforeach; ?>

  </div>

</section>



<?php if ($aiSpotlight):
    $sm = $aiSpotlight['m'];
    $st1 = trim($sm['team1'] ?? '');
    $st2 = trim($sm['team2'] ?? '');
    $sid = (int)($sm['_index'] ?? 0);
    $sFinished = isset($sm['score']['ft']) && is_array($sm['score']['ft']);
?>
<section class="fb-ai-spotlight">
  <div class="fb-ai-spot-head">
    <h2><?= e(t('ai_spotlight_title')) ?></h2>
    <p class="muted"><?= e(t('ai_spotlight_sub')) ?></p>
  </div>
  <article class="fb-ai-spot-card">
    <div class="fb-ai-spot-meta">
      <div class="ai-flags"><?= flag_img($st1, 'w40') ?><span class="ai-vs"><?= e(t('vs')) ?></span><?= flag_img($st2, 'w40') ?></div>
      <h3>
        <a href="<?= e(url('match.php', ['id' => $sid])) ?>">
          <?= e(team_name($st1)) ?> <?= e(t('vs')) ?> <?= e(team_name($st2)) ?>
        </a>
      </h3>
      <span class="ai-badge"><?= e($sFinished ? t('ai_summary') : t('ai_preview')) ?></span>
    </div>
    <?php
    $excerpt = '';
    foreach (preg_split('/\n+/', $aiSpotlight['text']) as $para) {
        $para = trim($para);
        if ($para === '') continue;
        $excerpt = $para;
        break;
    }
    if (strlen($excerpt) > 280) {
        $excerpt = mb_substr($excerpt, 0, 277, 'UTF-8') . '…';
    }
    ?>
    <p class="fb-ai-spot-text"><?= e($excerpt) ?></p>
    <a class="btn btn-sm" href="<?= e(url('match.php', ['id' => $sid])) ?>">
      <?= e($ar ? 'اقرأ التحليل الكامل ←' : 'Read full analysis →') ?>
    </a>
  </article>
</section>
<?php endif; ?>



<?php $opinion = DailyOpinion::block($ar); if ($opinion): ?>
<section class="fb-opinion">
  <div class="fb-opinion-head">
    <h2><?= e(t('opinion_title')) ?></h2>
    <span class="fb-opinion-date"><?= local_dt(time(), 'date') ?></span>
  </div>
  <blockquote class="fb-opinion-text"><?php
    $opinionHtml = e($opinion['text']);
    if (!empty($opinion['kickoff_ts']) && str_contains($opinionHtml, '{{KICKOFF}}')) {
        $opinionHtml = str_replace('{{KICKOFF}}', local_dt((int)$opinion['kickoff_ts'], 'time'), $opinionHtml);
    }
    echo $opinionHtml;
  ?></blockquote>
  <a class="btn btn-sm" href="<?= e($opinion['url']) ?>"><?= e($ar ? 'صفحة المباراة ←' : 'Match page →') ?></a>
</section>
<?php endif; ?>



<!-- ============ مباريات اليوم ============ -->

<section class="fb-block" id="today" data-autorefresh="1">

  <div class="fb-block-head">

    <h2><?= e(t('today_matches')) ?></h2>

    <span class="fb-block-date"><?= local_dt(time(), 'date') ?></span>

  </div>

  <?php if ($today): ?>

    <div class="fb-matches">

      <?php foreach ($today as $m) render_match_card($m); ?>

    </div>

  <?php else: ?>

    <p class="empty-note"><?= e(t('no_matches_today')) ?></p>

  <?php endif; ?>

</section>



<!-- ============ القادم + النتائج (القادم أولاً) ============ -->

<?php if ($results || $upcoming): ?>

<div class="fb-cols">

  <?php if ($upcoming): ?>

  <section class="fb-block">

    <div class="fb-block-head">

      <h2><?= e(t('upcoming')) ?></h2>

      <a class="fb-block-link" href="<?= e(url('matches.php', ['status' => 'upcoming'])) ?>"><?= e(t('all')) ?> ›</a>

    </div>

    <div class="fb-matches">

      <?php foreach ($upcoming as $m) render_match_card($m); ?>

    </div>

    <div class="fb-more">

      <a class="btn-ghost" href="<?= e(url('matches.php', ['status' => 'upcoming'])) ?>"><?= e(t('more_matches')) ?> ›</a>

    </div>

  </section>

  <?php endif; ?>



  <?php if ($results): ?>

  <section class="fb-block">

    <div class="fb-block-head">

      <h2><?= e(t('latest_results')) ?></h2>

      <a class="fb-block-link" href="<?= e(url('matches.php', ['status' => 'finished'])) ?>"><?= e(t('all')) ?> ›</a>

    </div>

    <div class="fb-matches">

      <?php foreach ($results as $m) render_match_card($m); ?>

    </div>

    <div class="fb-more">

      <a class="btn-ghost" href="<?= e(url('matches.php', ['status' => 'finished'])) ?>"><?= e(t('more_matches')) ?> ›</a>

    </div>

  </section>

  <?php endif; ?>

</div>

<?php endif; ?>



<!-- ============ fb-about — Bento Manifest ============ -->

<section class="fb-about" aria-labelledby="hi-title">

  <div class="fb-ab-wrap">

    <div class="fb-ab-head">

      <img class="fb-ab-logo" src="<?= e($logoUrl) ?>" alt="" width="120" height="36" loading="lazy">

      <span class="fb-ab-tag"><?= e(t('home_intro_tag')) ?></span>

      <span class="fb-ab-badge"><?= e(t('home_intro_badge')) ?></span>

    </div>

    <div class="fb-ab-grid">

      <div class="fb-ab-text">

        <h2 id="hi-title"><?= e(t('home_intro_title')) ?></h2>

        <p><?= e(t('home_intro_body')) ?></p>

      </div>

      <div class="fb-ab-tiles">

        <?php foreach ([

          ['⚽', t('home_intro_1')],

          ['📊', t('home_intro_2')],

          ['🎯', t('home_intro_3')],

          ['🔌', t('home_intro_4')],

        ] as [$ico, $txt]): ?>

          <div class="fb-ab-tile">

            <span class="fb-ab-tile-ico" aria-hidden="true"><?= $ico ?></span>

            <span class="fb-ab-tile-txt"><?= e($txt) ?></span>

          </div>

        <?php endforeach; ?>

      </div>

    </div>

  </div>

</section>



<!-- ============ تحليلات foot-boll — مقالات أصلية ============ -->

<?php
$insightLatest = Insights::latest();
$insightMore   = array_slice(Insights::all(), 0, 3);
if ($insightMore):
?>
<section class="fb-insights">
  <div class="fb-insights-head">
    <h2>📝 <?= e(t('insights_title')) ?></h2>
    <a class="fb-block-link" href="<?= e(url('insights.php')) ?>"><?= e(t('insights_more')) ?> ›</a>
  </div>
  <?php if ($insightLatest): ?>
    <article class="insight-card insight-card-featured" style="margin-bottom:16px">
      <span class="insight-date"><?= local_dt(strtotime(($insightLatest['published'] ?? date('Y-m-d')) . ' 12:00:00'), 'date') ?></span>
      <h3><a href="<?= e(Insights::url($insightLatest)) ?>"><?= e(Insights::field($insightLatest, 'title')) ?></a></h3>
      <p class="insight-excerpt"><?= e(Insights::field($insightLatest, 'excerpt')) ?></p>
      <a class="btn btn-sm" href="<?= e(Insights::url($insightLatest)) ?>"><?= e($ar ? 'اقرأ المقال ←' : 'Read article →') ?></a>
    </article>
  <?php endif; ?>
  <?php if (count($insightMore) > 1): ?>
    <div class="insights-grid insights-grid--sm">
      <?php foreach (array_slice($insightMore, 1, 2) as $it): ?>
        <article class="insight-card">
          <h3><a href="<?= e(Insights::url($it)) ?>"><?= e(Insights::field($it, 'title')) ?></a></h3>
          <p class="insight-excerpt"><?= e(Insights::field($it, 'excerpt')) ?></p>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>
<?php endif; ?>



<!-- ============ آخر الأخبار — بطاقات مربعة ============ -->

<?php $news = News::latest(4); ?>

<?php if ($news): ?>

<section class="fb-block">

  <div class="fb-block-head">

    <h2>📰 <?= e(t('latest_news')) ?></h2>

    <a class="fb-block-link" href="<?= e(url('news.php')) ?>"><?= e(t('news_more')) ?> ›</a>

  </div>

  <div class="fb-feed fb-feed-grid">

    <?php foreach ($news as $it) render_news_item($it, 'card'); ?>

  </div>

</section>

<?php endif; ?>



<!-- ============ بطولات مميزة ============ -->

<?php $featuredCups = FeaturedCups::cards(); ?>
<section class="fb-featured-cups">
  <div class="fb-fc-head">
    <h2>👑 <?= e(t('featured_cups_title')) ?></h2>
    <a class="fb-block-link" href="<?= e(url('featured.php')) ?>"><?= e(t('featured_cups_more')) ?> ›</a>
  </div>
  <p class="muted fb-fc-sub"><?= e(t('featured_cups_sub')) ?></p>
  <div class="fc-grid fc-grid--home">
    <?php foreach (array_slice($featuredCups, 0, 4) as $c): ?>
      <article class="fc-card<?= !empty($c['current']) ? ' fc-card-current' : '' ?>">
        <div class="fc-card-top">
          <span class="fc-year"><?= (int)$c['year'] ?></span>
          <span class="fc-tag"><?= e($c['tag']) ?></span>
        </div>
        <?php if (!empty($c['flag'])): ?>
          <div class="fc-winner">
            <?= flag_img_iso($c['flag'], 'w40') ?>
            <span><?= e($c['winner']) ?></span>
          </div>
        <?php endif; ?>
        <p class="fc-hook"><?= e($c['hook']) ?></p>
        <a class="fb-block-link" href="<?= e($c['url']) ?>"><?= e($ar ? 'استكشف ←' : 'Explore →') ?></a>
      </article>
    <?php endforeach; ?>
  </div>
</section>



<!-- ============ روابط سريعة + رسمية ============ -->

<?php

$quickLinks = [

  ['groups.php',   '▦', t('groups')],

  ['knockout.php', '🏆', t('knockout')],

  ['teams.php',    '⚑', t('teams')],

  ['stadiums.php', '🏟', t('stadiums')],

];

$officialLinks = [

  ['🌐', $ar ? 'الموقع الرسمي (FIFA)' : 'Official FIFA site', 'https://www.fifa.com/en/tournaments/mens/worldcup/canadamexicousa2026', true],

  ['🎟️', $ar ? 'التذاكر' : 'Tickets',                         'https://www.fifa.com/tickets',                                        true],

  ['🗺️', $ar ? 'خريطة المدن' : 'Host cities map',              url('map.php'),                                                         false],

  ['📅', $ar ? 'جدول المباريات' : 'Match schedule',            url('matches.php'),                                                     false],

  ['🏟️', $ar ? 'الملاعب' : 'Stadiums',                         url('stadiums.php'),                                                    false],

  ['🧭', $ar ? 'دليل المشجّع' : 'Fan guide',                   url('fanguide.php'),                                                    false],

];

?>

<section class="fb-pills">

  <p class="fb-pills-head"><?= e($ar ? 'تصفّح أقسام البطولة' : 'Browse tournament sections') ?></p>

  <div class="fb-pills-row">

    <?php foreach ($quickLinks as [$page, $icon, $label]): ?>

      <a class="fb-pill" href="<?= e(url($page)) ?>">

        <span aria-hidden="true"><?= $icon ?></span> <?= e($label) ?>

      </a>

    <?php endforeach; ?>

  </div>

  <p class="fb-pills-head"><?= e($ar ? 'مصادر FIFA والبطولة' : 'FIFA & tournament sources') ?></p>

  <div class="fb-pills-row">

    <?php foreach ($officialLinks as [$icon, $label, $href, $external]): ?>

      <a class="fb-pill" href="<?= e($href) ?>"<?= $external ? ' target="_blank" rel="noopener nofollow"' : '' ?>>

        <span aria-hidden="true"><?= $icon ?></span> <?= e($label) ?><?= $external ? ' ↗' : '' ?>

      </a>

    <?php endforeach; ?>

  </div>

</section>



<?php tpl('footer'); ?>
