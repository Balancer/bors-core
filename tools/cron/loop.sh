#!/bin/sh

php do-tasks.php
php statfile-cache-clean.php
php access_log_expire.php
