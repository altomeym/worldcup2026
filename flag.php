<?php
/**
 * flag.php — بروكسي صور الأعلام (نفس نطاق الموقع).
 * مثال: /flag.php?c=ch&s=w80
 */
define('WC2026', true);

require __DIR__ . '/includes/config.php';
require __DIR__ . '/includes/helpers.php';
require __DIR__ . '/includes/seo.php';
require __DIR__ . '/includes/FlagProxy.php';

flag_proxy_serve($_GET['c'] ?? '', $_GET['s'] ?? 'w80');
