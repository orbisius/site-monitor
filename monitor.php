<?php

/**
Site Monitor

Description
-----------------------------------------------------------------------------------------------
The script will check if one or more sites are online and will send you a notificaiton.
It will stop after the maximum alerts is reached. At some point if the site is
still down the notifications will reset and you will start receiving them again.
When the site comes back online the flag file is deleted and you'll get another 
notification.

This script is useful when you have multiple domains or manage domains on behalf of your clients.
You want to be the first one to know when a website is down so you can quickly fix it.

Usage:
-----------------------------------------------------------------------------------------------
1. Edit this file and add your email in APP_ALERT_EMAIL field.
Some carriers allow you to send an email to a specific email address which can be converted into text.
e.g. 123456789@msg.telus.com for Telus customers.

2. Create a text file called: sites.txt in the same folder where this script resides.
Each site has to be on its own line. Comments are allowed and they should be prefixed by # sign
Empty lines are skipped.

3. Setup cron a job. Remove the slash \ before the 5; it was added because of phpdoc)

Call this script from a cron job every 15 minutes.
*\/5 0-23 * * * /usr/bin/php /path/to/monitor.php

4. If you run into issues or want to suggest a feature submit a ticket at: https://github.com/orbisius/site-monitor/issues

5. If you are receiving multiple emails (more than the limit) please create a tmp folder in the same folder
where the script resides. Make sure it has write permissions.

@author Svetoslav Marinov (SLAVI) <slavi at orbisius.com>
@link http://orbisius.com
@copyright (c) 2013, Svetoslav (SLAVI) Marinov
@license LGPL
*/
define('APP_ALERT_EMAIL', 'ENTER-YOUR-EMAIL-HERE'); // change this
define('APP_ALERT_LIMIT', 2);
define('APP_ALERT_RESET', 4 * 3600); // how much does it have to pass before we reset the alerts

define('APP_BASE_DIR', dirname(__FILE__));
define('APP_TMP_DIR', APP_BASE_DIR . '/tmp');

$host = empty($_SERVER['HTTP_HOST']) ? `hostname` : $_SERVER['HTTP_HOST'];

if (!file_exists(APP_BASE_DIR . '/sites.txt')) {
    die('Please create sites.txt in the current directory.' . "\n");
}

$sites = file(APP_BASE_DIR . '/sites.txt');

foreach ($sites as $site) {
    $alert = 0; // reset these variables for each site we process.
    $subject = 'alert: ';
    $message = '';

    $site = preg_replace('#\s#', '', $site);
    $site = preg_replace('#\#.*#', '', $site);
    $site = trim($site);
    
    if (empty($site)) {
        continue;
    }
    
    if (!preg_match('#^https?://#si', $site)) {
        $site = 'http://' . $site;
    }

    $alert_file = app_get_check_file($site);

    if (!app_check_site($site)) { // site is down
        $subject .= 'Offline';
        
        if (file_exists($alert_file)) {
            $check_times = file_get_contents($alert_file);
            $check_times = empty($check_times) ? 0 : $check_times;
            $last_checked = filemtime($alert_file);

            // Send a notification
            if ($check_times < APP_ALERT_LIMIT) {
                $alert = 1;
            } elseif (time() - $last_checked > APP_ALERT_RESET) {
                $check_times = 0;
                $alert = 1;
            }

            file_put_contents($alert_file, $check_times + 1);
        } else {
            file_put_contents($alert_file, 1);
            $alert = 1;
        }
    } else {
        if (file_exists($alert_file)) {
            $alert = 1;
            unlink($alert_file);
            $subject .= 'Online';
        }
    }

    if ($alert) { // create a short message so it fits in txt msg
        //$message = "Checked on: " . date('r') . "\n";
        $message .= "$site\n"; // Site: 

        mail(APP_ALERT_EMAIL, $subject, $message, "From: alerts@" . $host . "\r\n");
    }
}

/**
 * Generates the alert file based on the site name in TMP dir.
 * the file will match the domain name: e.g. domain_com.txt
 * @param string $site
 * @return string
 */
function app_get_check_file($site) {
    if (!is_dir(APP_TMP_DIR)) {
        mkdir(APP_TMP_DIR, 0777, 1);
    }

    $alert_file = $site;
    $alert_file = preg_replace('#^.+?:/+(?:www\.)?#si', '', $alert_file);
    $alert_file = preg_replace('#[^\w-]#si', '_', $alert_file);
    $alert_file = preg_replace('#_+#', '_', $alert_file);
    $alert_file = trim($alert_file, ' _');
    $alert_file = APP_TMP_DIR . '/' . $alert_file . '.txt';

    return $alert_file;
}

/**
 *
 * @param string $url
 * @return boolean
 */
function app_check_site($url) {
    $agent = "Mozilla/5.0 (compatible; OrbisiusSiteMonitor/1.0; +http://orbisius.com)";
    
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_USERAGENT, $agent);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_VERBOSE, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSLVERSION, 3);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    
    $content = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    curl_close($ch);
    
    if (($http_code >= 200 && $http_code <= 300) // (!empty($content) &&
            || $http_code == 301 || $http_code == 302 || $http_code == 307) { // allow redirects
        return true;
    } else {
        return false;
    }
}


