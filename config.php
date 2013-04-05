<?php

// Enter your emails here. e.g. you@yourdomain.com,support@domain.com
define('APP_ALERT_EMAIL', '');

define('APP_BASE_DIR', dirname(__FILE__));
define('APP_TMP_DIR', APP_BASE_DIR . '/tmp');
define('APP_ALERT_THRESHOLD', 2); // how many failures have to happen before we consider a site offline
define('APP_ALERT_LIMIT', 2); // send max that many alerts
define('APP_ALERT_RESET', 4 * 3600); // how much time does it have to pass before we reset the alerts
define('APP_HOST', empty($_SERVER['HTTP_HOST']) ? `hostname` : $_SERVER['HTTP_HOST']); // that's your servername
