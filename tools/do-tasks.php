<?php

define('BORS_CORE', '/var/www/.bors/bors-core');
define('BORS_LOCAL', '/var/www/.bors/bors-airbase');
define('BORS_HOST', '/var/www/balancer.ru/.bors-host');
	
require_once(BORS_CORE.'/config.php');

//set_loglevel(10, NULL);
//config_set('debug_mysql_queries_log', '/var/www/balancer.ru/htdocs/logs/sql-timig.log');

exit(intval(bors_tools_tasks::execute_task()));
