<?php

require_once('../config.php');

if(!config('access_log'))
	return;

echo "---[ access log clean ]---\n";

require_once('inc/filesystem.php');

main();
bors_exit();

function main()
{
	$dbh = new driver_mysql(config('bors_local_db'));
	$dbh->delete('bors_access_log', [
		'*priority' => 'low',
		'access_time<' => time()-600,
	]);
}
