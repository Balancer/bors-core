#!/bin/bash

pushd $(dirname $0) > /dev/null
php do-tasks.php $1
php statfile-cache-clean.php $1
php access_log_expire.php $1
php access_log_counting.php $1
popd > /dev/null
