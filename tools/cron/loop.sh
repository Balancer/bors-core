#!/bin/sh

php do-tasks.php $1
php statfile-cache-clean.php $1
php access_log_expire.php $1
