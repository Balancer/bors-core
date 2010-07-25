<?php

require_once('../config.php');
include_once(BORS_CORE.'/init.php');

echo "---[ access log counting ]---\n";

$db = new driver_mysql(config('bors_core_db'));
$db->query('DELETE FROM bors_access_log WHERE access_time < UNIX_TIMESTAMP() - 3600');

foreach(objects_array('bors_access_log', array('was_counted' => 0)) as $x)
{
	if(!$x->is_bot() && $target = $x->target())
	{
		bors_external_referer::register($x->server_uri(), $x->referer(), $target);
		$target->visits_inc();
		$x->set_was_counted(1, true);
		echo "+";
	}
	else
	{
		bors_external_referer::register($x->server_uri(), $x->referer(), NULL);
		$x->set_was_counted(2, true);
		echo ".";
	}
}

echo "\n";
bors_exit();
