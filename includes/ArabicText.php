<?php
/**
 * ArabicText.php — تشكيل الحروف العربية للرسم في GD.
 * ============================================================
 * GD يرسم الحروف معزولةً وبترتيب يسار→يمين، فيخرج العربي مقطّعاً ومعكوساً.
 * هذا الصنف يحوّل النص المنطقي إلى:
 *   1) أشكال العرض الصحيحة (Presentation Forms-B): ابتدائي/وسطي/نهائي/معزول
 *      + لام-ألف كحرف واحد (لا لأ لإ لآ).
 *   2) ترتيب بصري (معكوس) مع إبقاء الأرقام واللاتيني باتجاههم الطبيعي.
 * النتيجة تُمرَّر مباشرة لـ imagettftext مع خط يدعم العربية (Cairo).
 * ============================================================
 */
if (!defined('WC2026')) { exit('Access denied'); }

class ArabicText
{
    /**
     * جدول أشكال العرض: حرف → [معزول, نهائي, ابتدائي, وسطي]
     * الحروف اليمينية الاتصال (لا تتصل بما بعدها) لها شكلان فقط.
     */
    private const FORMS = [
        'ء' => [0xFE80, 0xFE80, null,   null  ],
        'آ' => [0xFE81, 0xFE82, null,   null  ],
        'أ' => [0xFE83, 0xFE84, null,   null  ],
        'ؤ' => [0xFE85, 0xFE86, null,   null  ],
        'إ' => [0xFE87, 0xFE88, null,   null  ],
        'ئ' => [0xFE89, 0xFE8A, 0xFE8B, 0xFE8C],
        'ا' => [0xFE8D, 0xFE8E, null,   null  ],
        'ب' => [0xFE8F, 0xFE90, 0xFE91, 0xFE92],
        'ة' => [0xFE93, 0xFE94, null,   null  ],
        'ت' => [0xFE95, 0xFE96, 0xFE97, 0xFE98],
        'ث' => [0xFE99, 0xFE9A, 0xFE9B, 0xFE9C],
        'ج' => [0xFE9D, 0xFE9E, 0xFE9F, 0xFEA0],
        'ح' => [0xFEA1, 0xFEA2, 0xFEA3, 0xFEA4],
        'خ' => [0xFEA5, 0xFEA6, 0xFEA7, 0xFEA8],
        'د' => [0xFEA9, 0xFEAA, null,   null  ],
        'ذ' => [0xFEAB, 0xFEAC, null,   null  ],
        'ر' => [0xFEAD, 0xFEAE, null,   null  ],
        'ز' => [0xFEAF, 0xFEB0, null,   null  ],
        'س' => [0xFEB1, 0xFEB2, 0xFEB3, 0xFEB4],
        'ش' => [0xFEB5, 0xFEB6, 0xFEB7, 0xFEB8],
        'ص' => [0xFEB9, 0xFEBA, 0xFEBB, 0xFEBC],
        'ض' => [0xFEBD, 0xFEBE, 0xFEBF, 0xFEC0],
        'ط' => [0xFEC1, 0xFEC2, 0xFEC3, 0xFEC4],
        'ظ' => [0xFEC5, 0xFEC6, 0xFEC7, 0xFEC8],
        'ع' => [0xFEC9, 0xFECA, 0xFECB, 0xFECC],
        'غ' => [0xFECD, 0xFECE, 0xFECF, 0xFED0],
        'ف' => [0xFED1, 0xFED2, 0xFED3, 0xFED4],
        'ق' => [0xFED5, 0xFED6, 0xFED7, 0xFED8],
        'ك' => [0xFED9, 0xFEDA, 0xFEDB, 0xFEDC],
        'ل' => [0xFEDD, 0xFEDE, 0xFEDF, 0xFEE0],
        'م' => [0xFEE1, 0xFEE2, 0xFEE3, 0xFEE4],
        'ن' => [0xFEE5, 0xFEE6, 0xFEE7, 0xFEE8],
        'ه' => [0xFEE9, 0xFEEA, 0xFEEB, 0xFEEC],
        'و' => [0xFEED, 0xFEEE, null,   null  ],
        'ى' => [0xFEEF, 0xFEF0, null,   null  ],
        'ي' => [0xFEF1, 0xFEF2, 0xFEF3, 0xFEF4],
        'ـ' => [0x0640, 0x0640, 0x0640, 0x0640],   // تطويل: يتصل من الجهتين
    ];

    /** لام-ألف: ألِف → [معزول, نهائي] */
    private const LAM_ALEF = [
        'آ' => [0xFEF5, 0xFEF6],
        'أ' => [0xFEF7, 0xFEF8],
        'إ' => [0xFEF9, 0xFEFA],
        'ا' => [0xFEFB, 0xFEFC],
    ];

    /** هل الحرف عربي له أشكال (يقبل اتصال ما قبله)؟ */
    private static function isArabic(string $ch): bool
    {
        return isset(self::FORMS[$ch]);
    }

    /** هل الحرف يتصل بما بعده (له شكل ابتدائي/وسطي)؟ */
    private static function joinsNext(string $ch): bool
    {
        return isset(self::FORMS[$ch]) && self::FORMS[$ch][2] !== null;
    }

    /**
     * shape() — يحوّل نصاً منطقياً إلى نص جاهز للرسم في GD (مُشكَّل + ترتيب بصري).
     */
    public static function shape(string $text): string
    {
        if (trim($text) === '') return $text;

        // أزل التشكيل (الحَرَكات) — تربك GD ولا تلزم للعناوين.
        $text = preg_replace('/[\x{064B}-\x{065F}\x{0670}]/u', '', $text);

        $chars = preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY);
        if (!$chars) return $text;

        // ── المرحلة 1: التشكيل (أشكال العرض + لام-ألف) ──
        $shaped = [];                 // عناصر: ['ch' => نص الحرف النهائي, 'ar' => bool]
        $n = count($chars);
        for ($i = 0; $i < $n; $i++) {
            $ch = $chars[$i];
            if (!self::isArabic($ch)) {
                $shaped[] = ['ch' => $ch, 'ar' => false];
                continue;
            }

            // اتصال ما قبل: آخر حرف عربي مضاف كان يتصل بما بعده
            $prevJoins = false;
            for ($k = count($shaped) - 1; $k >= 0; $k--) {
                if ($shaped[$k]['ar']) { $prevJoins = $shaped[$k]['joins'] ?? false; break; }
                break; // غير عربي يقطع الاتصال
            }

            // لام-ألف؟
            if ($ch === 'ل' && $i + 1 < $n && isset(self::LAM_ALEF[$chars[$i + 1]])) {
                [$iso, $fin] = self::LAM_ALEF[$chars[$i + 1]];
                $code = $prevJoins ? $fin : $iso;
                $shaped[] = ['ch' => mb_chr($code, 'UTF-8'), 'ar' => true, 'joins' => false];
                $i++; // التهمنا الألف
                continue;
            }

            // اتصال ما بعد: الحرف التالي عربي، والحالي يقبل الاتصال بما بعده
            $nextIsArabic = ($i + 1 < $n) && self::isArabic($chars[$i + 1]);
            $curJoinsNext = self::joinsNext($ch) && $nextIsArabic;

            $f = self::FORMS[$ch];
            if ($prevJoins && $curJoinsNext)      { $code = $f[3] ?? $f[1]; }   // وسطي
            elseif ($prevJoins)                    { $code = $f[1]; }            // نهائي
            elseif ($curJoinsNext)                 { $code = $f[2] ?? $f[0]; }   // ابتدائي
            else                                   { $code = $f[0]; }            // معزول

            $shaped[] = ['ch' => mb_chr($code, 'UTF-8'), 'ar' => true, 'joins' => $curJoinsNext];
        }

        // ── المرحلة 2: الترتيب البصري — اعكس الكل ثم أعد عكس مقاطع الأرقام/اللاتيني ──
        $visual = array_reverse(array_column($shaped, 'ch'));

        $out = [];
        $ltrRun = [];
        $flushRun = function () use (&$out, &$ltrRun) {
            if ($ltrRun) { $out = array_merge($out, array_reverse($ltrRun)); $ltrRun = []; }
        };
        foreach ($visual as $ch) {
            // أرقام/لاتيني/فواصلها (: / -) تبقى يسار→يمين
            if (preg_match('/^[0-9A-Za-z:\/\-.]$/u', $ch)) {
                $ltrRun[] = $ch;
            } else {
                $flushRun();
                $out[] = $ch;
            }
        }
        $flushRun();

        return implode('', $out);
    }
}
