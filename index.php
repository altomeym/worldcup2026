<?php

/**

 * index.php — الصفحة الرئيسية.

 */

require __DIR__ . '/includes/bootstrap.php';

require __DIR__ . '/templates/match_card.php';



$page_title = t('home');

$today      = DataService::matchesOnDate();

$upcoming   = DataService::upcomingMatches(6);

$results    = DataService::latestResults(6);

$finalM     = Bracket::finalMatch();

$dataOk     = DataService::isOk();

$ar         = (current_lang() === 'ar');

$logoUrl    = rtrim(SITE_URL, '/') . '/assets/img/logo.png';



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

<section class="champion-banner">

  <div class="champion-glow"></div>

  <span class="champion-trophy">🏆</span>

  <p class="champion-label"><?= e(t('final_winner')) ?></p>

  <div class="champion-name">

    <?= flag_img($champion, 'w160') ?>

    <h2><?= e(team_name($champion)) ?></h2>

  </div>

</section>

<?php else: ?>

<section class="hero">

  <div class="hero-bg"></div>

  <div class="hero-content">

    <p class="hero-kicker">FIFA WORLD CUP</p>

    <h1 class="hero-title">2026</h1>

    <div class="hero-hosts" aria-hidden="true">

      <img src="<?= e(flag_url_iso('ca', 'w80')) ?>" alt="" loading="eager" width="40" height="30">

      <img src="<?= e(flag_url_iso('mx', 'w80')) ?>" alt="" loading="eager" width="40" height="30">

      <img src="<?= e(flag_url_iso('us', 'w80')) ?>" alt="" loading="eager" width="40" height="30">

    </div>

    <p class="hero-sub"><?= e(t('hero_tagline')) ?></p>

    <div class="hero-cta">

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

    <div class="hero-stats-inline" aria-label="<?= e($ar ? 'البطولة في أرقام' : 'Tournament in numbers') ?>">

      <div class="sb-item"><span class="sb-num">48</span><span class="sb-lbl"><?= e(t('teams')) ?></span></div>

      <div class="sb-item"><span class="sb-num">104</span><span class="sb-lbl"><?= e(t('matches')) ?></span></div>

      <div class="sb-item"><span class="sb-num">16</span><span class="sb-lbl"><?= e(t('host_cities')) ?></span></div>

      <div class="sb-item"><span class="sb-num">3</span><span class="sb-lbl"><?= e($ar ? 'دول مضيفة' : 'Host nations') ?></span></div>

    </div>

  </div>

</section>

<?php endif; ?>



<?php if (!$dataOk): ?>

  <div class="alert"><?= e(t('no_data')) ?></div>

<?php endif; ?>



<!-- ============ مباريات اليوم (أولاً) ============ -->

<section class="section" id="today" data-autorefresh="1">

  <div class="section-head">

    <h2><?= e(t('today_matches')) ?></h2>

    <span class="section-date"><?= local_dt(time(), 'date') ?></span>

  </div>

  <?php if ($today): ?>

    <div class="match-grid">

      <?php foreach ($today as $m) render_match_card($m); ?>

    </div>

  <?php else: ?>

    <p class="empty-note"><?= e(t('no_matches_today')) ?></p>

  <?php endif; ?>

</section>



<!-- ============ النتائج + القادم (عمودان) ============ -->

<?php if ($results || $upcoming): ?>

<div class="home-split">

  <?php if ($results): ?>

  <section class="section">

    <div class="section-head">

      <h2><?= e(t('latest_results')) ?></h2>

      <a class="section-link" href="<?= e(url('matches.php', ['status' => 'finished'])) ?>"><?= e(t('all')) ?> ›</a>

    </div>

    <div class="match-grid">

      <?php foreach ($results as $m) render_match_card($m); ?>

    </div>

    <div class="more-wrap">

      <a class="btn-ghost" href="<?= e(url('matches.php', ['status' => 'finished'])) ?>"><?= e(t('more_matches')) ?> ›</a>

    </div>

  </section>

  <?php endif; ?>



  <?php if ($upcoming): ?>

  <section class="section">

    <div class="section-head">

      <h2><?= e(t('upcoming')) ?></h2>

      <a class="section-link" href="<?= e(url('matches.php', ['status' => 'upcoming'])) ?>"><?= e(t('all')) ?> ›</a>

    </div>

    <div class="match-grid">

      <?php foreach ($upcoming as $m) render_match_card($m); ?>

    </div>

    <div class="more-wrap">

      <a class="btn-ghost" href="<?= e(url('matches.php', ['status' => 'upcoming'])) ?>"><?= e(t('more_matches')) ?> ›</a>

    </div>

  </section>

  <?php endif; ?>

</div>

<?php endif; ?>



<!-- ============ home-intro — Bento Manifest ============ -->

<section class="home-intro" aria-labelledby="hi-title">

  <div class="hi-shell">

    <div class="hi-head">

      <img class="hi-logo" src="<?= e($logoUrl) ?>" alt="" width="120" height="36" loading="lazy">

      <span class="hi-tag"><?= e(t('home_intro_tag')) ?></span>

      <span class="hi-badge"><?= e(t('home_intro_badge')) ?></span>

    </div>

    <div class="hi-grid">

      <div class="hi-copy">

        <h2 id="hi-title"><?= e(t('home_intro_title')) ?></h2>

        <p><?= e(t('home_intro_body')) ?></p>

      </div>

      <div class="hi-bento">

        <?php foreach ([

          ['⚽', t('home_intro_1')],

          ['📊', t('home_intro_2')],

          ['🎯', t('home_intro_3')],

          ['🔌', t('home_intro_4')],

        ] as [$ico, $txt]): ?>

          <div class="hi-tile">

            <span class="hi-tile-ico" aria-hidden="true"><?= $ico ?></span>

            <span class="hi-tile-txt"><?= e($txt) ?></span>

          </div>

        <?php endforeach; ?>

      </div>

    </div>

  </div>

</section>



<!-- ============ الميزات ============ -->

<section class="engage">

  <div class="engage-head">

    <h2><?= e(t('engage_title')) ?></h2>

    <p class="muted"><?= e(t('engage_sub')) ?></p>

  </div>

  <div class="engage-grid">

    <?php

    $features = [

        ['predict.php',     '🎯', t('predict'),     t('f_predict')],

        ['bracket.php',     '🏆', t('bracket'),     t('f_bracket')],

        ['stickers.php',    '🃏', t('stickers'),    t('f_stickers')],

        ['trivia.php',      '❓', t('trivia'),      t('f_trivia')],

        ['leaderboard.php', '🏅', t('leaderboard'), t('f_leaderboard')],

        ['stats.php',       '📊', t('stats'),       t('f_stats')],

        ['topscorers.php',  '⚽', t('top_scorers'), t('f_scorers')],

    ];

    foreach ($features as [$page, $icon, $title, $desc]): ?>

      <a class="engage-card" href="<?= e(url($page)) ?>">

        <span class="engage-icon"><?= $icon ?></span>

        <span class="engage-body">

          <span class="engage-title"><?= e($title) ?></span>

          <span class="engage-desc"><?= e($desc) ?></span>

        </span>

      </a>

    <?php endforeach; ?>

  </div>

</section>



<!-- ============ آخر الأخبار — بطاقات مربعة ============ -->

<?php $news = News::latest(4); ?>

<?php if ($news): ?>

<section class="section">

  <div class="section-head">

    <h2>📰 <?= e(t('latest_news')) ?></h2>

    <a class="section-link" href="<?= e(url('news.php')) ?>"><?= e(t('news_more')) ?> ›</a>

  </div>

  <div class="news-list news-grid">

    <?php foreach ($news as $it) render_news_item($it, 'card'); ?>

  </div>

</section>

<?php endif; ?>



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

<section class="home-util">

  <p class="home-util-head"><?= e($ar ? 'استكشف البطولة' : 'Explore the tournament') ?></p>

  <div class="home-util-row">

    <?php foreach ($quickLinks as [$page, $icon, $label]): ?>

      <a class="home-util-pill" href="<?= e(url($page)) ?>">

        <span aria-hidden="true"><?= $icon ?></span> <?= e($label) ?>

      </a>

    <?php endforeach; ?>

  </div>

  <p class="home-util-head"><?= e($ar ? 'روابط رسمية ومفيدة' : 'Official & useful links') ?></p>

  <div class="home-util-row">

    <?php foreach ($officialLinks as [$icon, $label, $href, $external]): ?>

      <a class="home-util-pill" href="<?= e($href) ?>"<?= $external ? ' target="_blank" rel="noopener nofollow"' : '' ?>>

        <span aria-hidden="true"><?= $icon ?></span> <?= e($label) ?><?= $external ? ' ↗' : '' ?>

      </a>

    <?php endforeach; ?>

  </div>

</section>



<?php tpl('footer'); ?>

