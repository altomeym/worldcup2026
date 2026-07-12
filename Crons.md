# Cron Jobs — foot-boll.com (Namecheap)
# PHP: /usr/local/bin/php
# المسار: /home/footlulu/public_html/
#
# في cPanel → Cron Jobs: أضِف كل سطر «Command» على حدة.
# قبل أول تشغيل: mkdir -p /home/footlulu/logs

# ── إلزامي / موصى به ─────────────────────────────────────────────

# 1) حكّام + خريطة API-Football
0 */6 * * * /usr/local/bin/php /home/footlulu/public_html/cron/refs.php >> /home/footlulu/logs/refs.log 2>&1

# 2) تقارير المباريات (PDF) → assets/fifa/*.json فقط — من GitHub
0 * * * * /usr/local/bin/php /home/footlulu/public_html/cron/deploy.php >> /home/footlulu/logs/deploy.log 2>&1

# 3) مقاييس + صور + MOTM — من fifaphy مباشرة (بديل deploy للـ 3 ملفات)
0 * * * * /usr/local/bin/php /home/footlulu/public_html/cron/fifa-feed.php >> /home/footlulu/logs/fifa-feed.log 2>&1

# 4) تسخين النتائج المباشرة (أثناء البطولة فقط)
*/3 * * * * curl -sS "https://foot-boll.com/api/data.php?action=live" -o /dev/null

# ── اختياري ──────────────────────────────────────────────────────

# 5) نشرة بريد — يحتاج SMTP_* في config.local.php
# 0 9 * * * /usr/local/bin/php /home/footlulu/public_html/cron/digest.php >> /home/footlulu/logs/digest.log 2>&1

# 6) X/Twitter — يحتاج X_API_* في config.local.php
# */15 * * * * /usr/local/bin/php /home/footlulu/public_html/cron/tweet.php >> /home/footlulu/logs/tweet.log 2>&1


