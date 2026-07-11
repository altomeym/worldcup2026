# Contributing / المساهمة

Thanks for your interest in improving **World Cup 2026 Companion**! 🎉
شكراً لرغبتك في تحسين المشروع!

## Principles / المبادئ
- **No dependencies, no build step.** Plain PHP 8+ and vanilla JS/CSS only.
- **Security first:** escape every output with `e()`; protect state‑changing endpoints with CSRF; validate & clamp all input; **never commit secrets** (`config.local.php` is git‑ignored).
- **Graceful degradation:** a feature must never break the site when a key/DB is missing.
- **i18n:** add new UI strings to `includes/i18n.php` for **both** `ar` and `en`.
- **Keep it fast:** the data layer is cache‑first; never block a page render on a slow network call.

## Dev setup / الإعداد
```bash
cp includes/config.local.example.php includes/config.local.php
php -S 127.0.0.1:8000      # served from the project root
```
The site runs with zero keys (uses the bundled fallback + public openfootball feed).

## Before opening a PR / قبل فتح طلب الدمج
- Lint every PHP file:
  ```bash
  find . -name "*.php" -not -path "./assets/vendor/*" -print0 | xargs -0 -n1 php -l
  ```
- Test the pages you touched in **both** languages (`?lang=ar` and `?lang=en`).
- Keep the change focused; explain *what* and *why* in the PR description.
- Follow the existing code style (see `.editorconfig`).

## Reporting bugs / الإبلاغ عن الأخطاء
Use the issue templates and include: steps to reproduce, expected vs actual, PHP version, and environment.

## Security issues / الثغرات الأمنية
Do **not** open a public issue — see [SECURITY.md](SECURITY.md).


<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-4205749747374725"
     crossorigin="anonymous"></script>



G-F7D1889PSV
 Content security policy (CSP) blocking Google scripts
This page's security settings (Content Security Policy) are preventing scripts from Google domains like googletagmanager.com or google-analytics.com from loading or executing.
How to fix the issue:
Open your browser's Developer Tools and check the "Console" tab for errors mentioning CSP violations related to Google domains.
This issue needs to be resolved by a web developer or server administrator. They will need to update the website's Content-Security-Policy HTTP header to include the necessary Google domains in the script-src-elem, img-src and connect-src directives. Refer to Google's documentation for specific domains.


https://tagassistant.google.com/#/?url=https%3A%2F%2Ffoot-boll.com%2F


<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-F7D1889PSV"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-F7D1889PSV');
</script>


https://wcup2026.org/index.php?lang=en