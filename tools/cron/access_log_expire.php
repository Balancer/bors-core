<?php

require_once('../config.php');
	
require_once(BORS_CORE.'/config.php');

if(!config('access_log'))
	return;

echo "---[ access log clean ]---\n";

require_once('inc/filesystem.php');

main();
bors_exit();

function main()
{
	$dbh = new driver_mysql(config('bors_core_db'));
	$dbh->delete('bors_access_log', array('access_time<' => time()-3600));
}
