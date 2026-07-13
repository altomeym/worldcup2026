<?php
/**
 * FeaturedCups.php — بطولات مميزة (محتوى تحريري أصلي + روابط الأرشيف).
 */
if (!defined('WC2026')) { exit('Access denied'); }

class FeaturedCups
{
    /** نسخ مختارة مع سياق foot-boll — مرتّبة من الأحدث. */
    private static function defs(): array
    {
        return [
            [
                'year' => 2026,
                'tag_ar' => 'الحالية',
                'tag_en' => 'Current',
                'hook_ar' => 'أول مونديال بـ48 منتخباً عبر 3 دول — foot-boll يغطيها بتحليلات FIFA ومقارنات حية.',
                'hook_en' => 'First 48-team edition across 3 nations — foot-boll covers it with FIFA analytics and live comparisons.',
                'url' => 'index.php',
            ],
            [
                'year' => 2022,
                'tag_ar' => 'مونديال الشتاء',
                'tag_en' => 'Winter Cup',
                'hook_ar' => 'أول كأس عالم في الشرق الأوسط — نهائي الأرجنتين وفرنسا أعاد تعريف الدراما الحديثة.',
                'hook_en' => 'First Middle East World Cup — the Argentina–France final redefined modern drama.',
            ],
            [
                'year' => 2018,
                'tag_ar' => 'هدف مبابي',
                'tag_en' => 'Mbappé debut',
                'hook_ar' => 'فرنسا بطلة بجيل شاب — كرواتيا مفاجأة النهائي وأول VAR كامل في المونديال.',
                'hook_en' => 'France won with a young squad — Croatia shocked the final and VAR arrived fully.',
            ],
            [
                'year' => 2010,
                'tag_ar' => 'أول أفريقيا',
                'tag_en' => 'First Africa',
                'hook_ar' => 'جنوب أفريقيا استضافت الحلم القاري — إسبانيا أول لقب لها بتيكا تيكا مثالية.',
                'hook_en' => 'South Africa hosted the continental dream — Spain\'s first title with perfect tiki-taka.',
            ],
            [
                'year' => 2002,
                'tag_ar' => 'آسيا',
                'tag_en' => 'Asia',
                'hook_ar' => 'كوريا واليابان — أول مونديال مشترك، وبرازيل خامسة بروح رونالدينيو ورونالدو.',
                'hook_en' => 'Korea and Japan — first co-hosted Cup; Brazil\'s fifth with Ronaldinho and Ronaldo.',
            ],
            [
                'year' => 1986,
                'tag_ar' => 'يد مارادونا',
                'tag_en' => 'Maradona',
                'hook_ar' => 'المكسيك شهدت «يد الله» و«هدف القرن» — الأرجنتين بطلة بقيادة واحدة من أعظم اللاعبين.',
                'hook_en' => 'Mexico saw the Hand of God and Goal of the Century — Argentina led by an all-time great.',
            ],
            [
                'year' => 1970,
                'tag_ar' => 'الجيل الذهبي',
                'tag_en' => 'Golden team',
                'hook_ar' => 'البرازيل وبيليه في المكسيك — أول فريق يحتفظ بالكأس للأبد بكرة فنية خالدة.',
                'hook_en' => 'Brazil and Pelé in Mexico — the first team to keep the trophy forever with timeless football.',
            ],
        ];
    }

    /** بطاقات جاهزة للعرض مع بيانات البطل من الأرشيف. */
    public static function cards(?string $lang = null): array
    {
        $lang = ($lang === 'ar' || $lang === 'en') ? $lang : current_lang();
        $ar = ($lang === 'ar');
        $champByYear = [];
        foreach (ArchiveService::allChampions() as $c) {
            $champByYear[(int)$c['year']] = $c;
        }

        $out = [];
        foreach (self::defs() as $d) {
            $year = (int)$d['year'];
            $c = $champByYear[$year] ?? null;
            $host = $ar
                ? ($c['host'] ?? ($year === 2026 ? 'كندا · المكسيك · أمريكا' : ''))
                : ($c['host_en'] ?? ($year === 2026 ? 'Canada · Mexico · USA' : ''));
            $winner = $c ? ($ar ? $c['winner']['ar'] : $c['winner']['en']) : ($ar ? 'جارية الآن' : 'In progress');
            $flag = $c ? ($c['winner']['flag'] ?? '') : ($year === 2026 ? '' : '');
            $score = $c['score'] ?? '';

            $url = $d['url'] ?? null;
            if ($url === null && ArchiveService::isValidYear($year)) {
                $url = 'archive.php?year=' . $year;
            } elseif ($url === null) {
                $url = 'archive.php';
            }

            $out[] = [
                'year'   => $year,
                'tag'    => $ar ? $d['tag_ar'] : $d['tag_en'],
                'hook'   => $ar ? $d['hook_ar'] : $d['hook_en'],
                'host'   => $host,
                'winner' => $winner,
                'flag'   => $flag,
                'score'  => $score,
                'url'    => url($url),
                'current'=> ($year === 2026),
            ];
        }
        return $out;
    }
}
