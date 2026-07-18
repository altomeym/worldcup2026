<?php
/**
 * seo.php — أدوات تحسين الظهور في محركات البحث.
 * ------------------------------------------------------------
 *  base_url()        : عنوان الموقع المطلق (من SITE_URL أو من الطلب الحالي)
 *  page_url_lang()   : رابط الصفحة الحالية بلغة محددة (لبدائل hreflang)
 *  seo_head()        : يطبع canonical + hreflang + Open Graph/Twitter + JSON-LD عام
 *  seo_sportsevent() : JSON-LD لمباراة واحدة (SportsEvent)
 *  seo_breadcrumb()  : JSON-LD مسار تنقّل (BreadcrumbList)
 * ------------------------------------------------------------
 */
if (!defined('WC2026')) { exit('Access denied'); }

/** عنوان الموقع المطلق بلا شرطة في النهاية */
function base_url(): string {
    if (defined('SITE_URL') && SITE_URL !== '') {
        return rtrim(SITE_URL, '/');
    }
    $https  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
           || (($_SERVER['SERVER_PORT'] ?? '') == 443);
    $scheme = $https ? 'https' : 'http';
    $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
    return $scheme . '://' . $host;
}

/**
 * مفاتيح الاستعلام المسموح بها في الروابط القانونية (canonical / hreflang).
 * أي شيء آخر (utm_*, fbclid, …) يُستبعد لتفادي نسخ مكرّرة.
 */
function seo_allowed_query_keys(): array {
    return [
        'id', 'team', 'slug', 'year', 'i', 'g', 'a', 'b',
        'u', 'code', 'name', 'round', 'group', 'status',
    ];
}

/** يجمع معاملات الصفحة المهمة فقط (بلا lang) */
function seo_content_query_params(): array {
    $params = [];
    foreach (seo_allowed_query_keys() as $key) {
        if (!isset($_GET[$key])) {
            continue;
        }
        $val = $_GET[$key];
        if (is_array($val) || $val === '') {
            continue;
        }
        $params[$key] = $val;
    }
    return $params;
}

/**
 * رابط الصفحة الحالية بلغة محددة.
 * الرئيسية العربية → /  |  الإنجليزية → /?lang=en
 */
function page_url_lang(string $lang): string {
    $baseName = basename((string)($_SERVER['SCRIPT_NAME'] ?? 'index.php'));
    $params   = seo_content_query_params();

    if ($baseName === 'index.php') {
        if ($lang !== 'ar') {
            $params = array_merge(['lang' => $lang], $params);
        }
        $qs = http_build_query($params);
        return base_url() . '/' . ($qs ? '?' . $qs : '');
    }

    $ordered = array_merge(['lang' => $lang], $params);
    $qs = http_build_query($ordered);
    return base_url() . '/' . $baseName . ($qs ? '?' . $qs : '');
}

/** الرابط القانوني للصفحة الحالية (باللغة الحالية) */
function canonical_url(): string {
    return page_url_lang(current_lang());
}

/**
 * seo_head() — يُطبع كل وسوم الـSEO داخل <head>.
 * $opts: ['title'=>..., 'description'=>..., 'image'=>..., 'type'=>'website|article']
 */
function seo_head(array $opts = []): void {
    $lang  = current_lang();
    $title = $opts['title'] ?? t('site_desc');
    $desc  = $opts['description'] ?? t('site_desc');
    // og:image الافتراضي = البطاقة الديناميكية بالهوية الحالية (تتحدّث تلقائياً عند تغيير التصميم).
    // الـcache-buster مبنيٌّ على mtime ملف المولّد → URL جديد عند كل تعديل → تويتر/فيسبوك يُعيدان الجلب.
    $defaultOg = base_url() . '/card_img.php?v=' . (@filemtime(__DIR__ . '/../card_img.php') ?: 1);
    $image = $opts['image'] ?? $defaultOg;
    $type  = $opts['type'] ?? 'website';
    $canon = canonical_url();
    $siteName = ($lang === 'ar') ? SITE_NAME_AR : SITE_NAME_EN;

    echo '<link rel="canonical" href="' . e($canon) . '">' . "\n";
    echo '<link rel="alternate" hreflang="ar" href="' . e(page_url_lang('ar')) . '">' . "\n";
    echo '<link rel="alternate" hreflang="en" href="' . e(page_url_lang('en')) . '">' . "\n";
    echo '<link rel="alternate" hreflang="x-default" href="' . e(page_url_lang('ar')) . '">' . "\n";

    // Open Graph
    echo '<meta property="og:site_name" content="' . e($siteName) . '">' . "\n";
    $ogLocale = ($lang === 'ar') ? 'ar_AR' : 'en_US';
    echo '<meta property="og:locale" content="' . e($ogLocale) . '">' . "\n";
    echo '<meta property="og:type" content="' . e($type) . '">' . "\n";
    echo '<meta property="og:title" content="' . e($title) . '">' . "\n";
    echo '<meta property="og:description" content="' . e($desc) . '">' . "\n";
    echo '<meta property="og:url" content="' . e($canon) . '">' . "\n";
    $imgW = !empty($opts['image_w']) ? (int)$opts['image_w'] : 1200;
    $imgH = !empty($opts['image_h']) ? (int)$opts['image_h'] : 630;
    echo '<meta property="og:image" content="' . e($image) . '">' . "\n";
    echo '<meta property="og:image:width" content="' . $imgW . '">' . "\n";
    echo '<meta property="og:image:height" content="' . $imgH . '">' . "\n";

    // Twitter
    echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
    echo '<meta name="twitter:title" content="' . e($title) . '">' . "\n";
    echo '<meta name="twitter:description" content="' . e($desc) . '">' . "\n";
    echo '<meta name="twitter:image" content="' . e($image) . '">' . "\n";

    // JSON-LD: WebSite + Organization (في كل الصفحات)
    $ld = [
        '@context' => 'https://schema.org',
        '@graph'   => [
            [
                '@type' => 'WebSite',
                '@id'   => base_url() . '/#website',
                'url'   => base_url() . '/',
                'name'  => $siteName,
                'description' => $desc,
                'inLanguage'  => $lang,
            ],
            [
                '@type' => 'Organization',
                '@id'   => base_url() . '/#org',
                'name'  => $siteName,
                'url'   => base_url() . '/',
                'logo'  => base_url() . '/assets/img/og.png',
            ],
        ],
    ];
    // JSON_HEX_TAG/AMP يهرّبان < > & (إلى \u00XX) فيستحيل الخروج من وسم <script> مهما كانت البيانات.
    echo '<script type="application/ld+json">'
       . json_encode($ld, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP)
       . '</script>' . "\n";
}

/**
 * seo_sportsevent() — JSON-LD لمباراة (نوع SportsEvent) لتحسين ظهورها.
 */
function seo_sportsevent(array $m): void {
    $ts     = DataService::matchTimestamp($m);
    $raw1   = trim($m['team1'] ?? '');
    $raw2   = trim($m['team2'] ?? '');
    $t1     = team_name($raw1);
    $t2     = team_name($raw2);
    $name   = $t1 . ' ' . t('vs') . ' ' . $t2;
    $ar     = (current_lang() === 'ar');
    $status = $m['_status'] ?? DataService::matchStatus($m);

    // صورة المباراة: نفس بطاقة المشاركة المُولّدة التي تستخدمها صفحة match.php
    $idx   = (int)($m['_index'] ?? 0);
    $image = base_url() . '/card.php?id=' . $idx . '&mode=match&v=3';

    // وصف موجز ودقيق للمباراة (الحقل description المطلوب من Google)
    $bits = [];
    if (!empty($m['round']))  { $bits[] = round_label($m['round']); }
    if (!empty($m['group']))  { $bits[] = group_label($m['group']); }
    if (!empty($m['ground'])) { $bits[] = $m['ground']; }
    $desc = $name . ($bits ? ' — ' . implode(' · ', $bits) : '')
          . ' — ' . ($ar ? 'نتائج وإحصائيات كأس العالم 2026 على foot-boll.com' : 'World Cup 2026 scores and stats on foot-boll.com');

    // الفريقان (يُستخدمان في competitor الدقيق + performer الذي يطلبه Google)
    $teamLd = static function (string $raw, string $display): array {
        $entry = ['@type' => 'SportsTeam', 'name' => $display];
        if ($raw !== '' && is_real_team($raw)) {
            $entry['url'] = url('team.php', ['team' => $raw]);
        }
        return $entry;
    };
    $teams = [$teamLd($raw1, $t1), $teamLd($raw2, $t2)];

    $ld = [
        '@context'    => 'https://schema.org',
        '@type'       => 'SportsEvent',
        'name'        => $name,
        'description' => $desc,
        'sport'       => 'Football',
        'url'         => canonical_url(),
        'image'       => [$image],
    ];
    if ($ts !== null) {
        $ld['startDate'] = gmdate('c', $ts);
        $ld['endDate']   = gmdate('c', $ts + 7200);   // ~ساعتان مدّة المباراة
        if ($status === 'upcoming' || $status === 'live') {
            $ld['eventStatus'] = 'https://schema.org/EventScheduled';
        }
    }
    if (!empty($m['ground'])) {
        $ld['location'] = [
            '@type'   => 'Place',
            'name'    => $m['ground'],
            'address' => [
                '@type'           => 'PostalAddress',
                'addressLocality' => $m['ground'],
            ],
        ];
    }
    $ld['competitor'] = $teams;                 // الدقيق دلالياً لرياضة
    $ld['performer']  = $teams;                 // ← performer (يطلبه Google لنتائج Event)
    $ld['organizer'] = [
        '@type' => 'Organization',
        'name'  => 'FIFA',
        'url'   => 'https://www.fifa.com/',
    ];
    // offers — حقل يوصي به Google لأحداث Event. الموقع لا يبيع تذاكر، فلا نختلق
    // سعراً؛ نوجّه للمصدر الرسمي (FIFA) مع حالة التوفّر حسب كون المباراة قادمة.
    $ld['offers'] = [
        '@type'        => 'Offer',
        'url'          => 'https://www.fifa.com/en/tournaments/mens/worldcup/canadamexicousa2026/tickets',
        'availability' => ($ts !== null && $ts > time())
                          ? 'https://schema.org/InStock'
                          : 'https://schema.org/SoldOut',
    ];

    // JSON_HEX_TAG/AMP يهرّبان < > & (إلى \u00XX) فيستحيل الخروج من وسم <script> مهما كانت البيانات.
    echo '<script type="application/ld+json">'
       . json_encode($ld, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP)
       . '</script>' . "\n";
}

/**
 * seo_breadcrumb() — JSON-LD مسار تنقّل (BreadcrumbList).
 * $items: [['name'=>..., 'url'=>...?], ...] — العنصر الأخير بلا url = الصفحة الحالية.
 */
function seo_breadcrumb(array $items): void {
    if (count($items) < 2) {
        return;
    }
    $list = [];
    foreach ($items as $i => $item) {
        $entry = [
            '@type'    => 'ListItem',
            'position' => $i + 1,
            'name'     => (string)($item['name'] ?? ''),
        ];
        if (!empty($item['url'])) {
            $entry['item'] = (string)$item['url'];
        }
        $list[] = $entry;
    }
    $ld = [
        '@context'        => 'https://schema.org',
        '@type'           => 'BreadcrumbList',
        'itemListElement' => $list,
    ];
    echo '<script type="application/ld+json">'
       . json_encode($ld, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP)
       . '</script>' . "\n";
}
