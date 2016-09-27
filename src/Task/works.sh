#!/bin/bash

while [ 1 ]
do
	php ./works.php >> /var/log/cron/json-tasks.log 2>&1
done
