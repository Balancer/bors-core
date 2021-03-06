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

$db->query('DELETE LOW_PRIORITY FROM bors_access_log WHERE access_time < '.(time() - 600));

foreach(bors_find_all('bors_access_log', array('was_counted' => 0)) as $x)
{
	if(in_array($x->target_class_name(), ['bors_tools_ajax_module', 'bors_system_js_touch', 'bors_cg_f2f_css_less', 'bors_data_lists_json']))
		continue;

//	if(preg_match($x->referer())
//		continue;

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
