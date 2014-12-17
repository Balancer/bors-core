<?php

require_once('../config.php');

echo "---[ access log counting ]---\n";

if(!config('bors_core_db'))
	exit();

try{
	$db = new driver_mysql(config('bors_local_db'));
}
catch(Exception $e)
{
	echo "Exception $e";
	exit();
}

$db->query('DELETE FROM bors_access_log WHERE access_time < UNIX_TIMESTAMP() - 600');

foreach(objects_array('bors_access_log', array('was_counted' => 0)) as $x)
{
	if(!$x->is_bot() && ($target = $x->target()))
	{
		try {
			bors_external_referer::register($x->server_uri(), $x->referer(), $target);
		} catch(Exception $e) { }
#		$target->visits_inc();
		$x->set_was_counted(1, true);
		echo "+";
	}
	else
	{
#		bors_external_referer::register($x->server_uri(), $x->referer(), NULL);
		$x->set_was_counted(2, true);
		echo ".";
	}
}

bors()->changed_save();

echo "\n";
bors_exit();
