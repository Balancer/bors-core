#!/bin/bash

while [ 1 ]; do
	php `dirname $0`/do-tasks.php && sleep 10
	sleep 1
done
