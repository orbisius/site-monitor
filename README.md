site-monitor
============

Site Monitor

Description
-----------------------------------------------------------------------------------------------
The script will check if one or more sites are online and will send you a notificaiton.
It will stop after the maximum alerts is reached. At some point if the site is
still down the notifications will reset and you will start receiving them again.
When the site comes back online the flag file is deleted and you'll get another
notification. The notification emails are comined into one so you won't get multiple emails.

This script is useful when you have multiple domains or manage domains on behalf of your clients.
You want to be the first one to know when a website is down so you can quickly fix it.

Usage:
-----------------------------------------------------------------------------------------------

0. Download site-monitor in a folder of your choice
git clone https://github.com/orbisius/site-monitor.git

1. Edit the config.php file and add your email(s) in APP_ALERT_EMAIL field.
Some carriers allow you to send an email to a specific email address which can be converted into text.
e.g. 123456789@msg.telus.com for Telus customers.

2. Create a text file called: sites.txt in the same folder where this script resides.
You can check sites.sample.txt for some ideas. Each site has to be on its own line.
Comments are allowed and they should be prefixed by # (pound) sign. Empty lines are skipped.

3. Setup cron a job.
Running the script from console (cli) is the recommended approach as opposed to using lynx because
if your site is down the monitoring script won't get executed.

Runs this script from a cron job every 5 minutes.
*/5 0-23 * * * /usr/bin/php /path/to/monitor.php

4. If you run into issues or want to suggest a feature submit a ticket at: https://github.com/orbisius/site-monitor/issues

5. If you are receiving multiple emails (more than the limit) please create a tmp folder in the same folder
where the script resides. Make sure it has write permissions.

Svetoslav Marinov (SLAVI) <slavi at orbisius.com>
http://orbisius.com
copyright (c) 2013, Svetoslav (SLAVI) Marinov
license LGPL
