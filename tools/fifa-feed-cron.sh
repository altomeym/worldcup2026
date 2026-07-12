#!/bin/bash
# fifa-feed-cron.sh — يحدّث fifa-metrics.json + fifa-photos.json + fifa-motm.json
# من feed fifaphy (Linux/cron فقط). على Namecheap يُفضَّل: cron/fifa-feed.php
#
# Cron: 0 * * * * /bin/bash /home/USER/public_html/tools/fifa-feed-cron.sh >> /home/USER/logs/fifa-feed.log 2>&1

FEED="$(cd "$(dirname "$0")" && pwd)/_feed"
PHP="${PHP_BIN:-/usr/local/bin/php}"
ROOT="$(cd "$(dirname "$0")/.." && pwd)"

mkdir -p "$FEED" || exit 1
cd "$FEED" || exit 1

curl -sfS -o data.js    https://fifaphy.vercel.app/data.js    || exit 1
curl -sfS -o ratings.js https://fifaphy.vercel.app/ratings.js || exit 1
curl -sfS -o posreal.js https://fifaphy.vercel.app/posreal.js || exit 1

"$PHP" "$ROOT/tools/fifa-metrics-build.php" "$FEED"
