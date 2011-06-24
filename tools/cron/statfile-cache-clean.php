---[ Statcache files clean ]---
<?php
$start = time();

require_once('../config.php');
require_once(BORS_CORE.'/init.php');

	require_once('obsolete/DataBase.php');
	require_once('inc/processes.php');

	if(!bors_thread_lock('statfile-cache-clean', 600))
		exit("Locked\n");

	if(!config('cache_database'))
		exit();

	$db = new driver_mysql(config('cache_database'));

	foreach($db->get_array("SELECT file, recreate, class_id, object_id, original_uri FROM cached_files WHERE expire_time BETWEEN 0 AND ".time()) as $x)
	{
		echo "{$x['original_uri']}, {$x['file']} [recreate={$x['recreate']}]: ";


		if(!$obj = object_load($x['original_uri']))
			$obj = object_load($x['class_id'], $x['object_id']);

		if($obj)
			$db->delete('cache_groups', array('_target_class_id' => $obj->class_id(), '_target_object_id' => $obj->id()));

		$db->query("DELETE FROM cached_files WHERE file = '".addslashes($x['file'])."'");

		if($x['recreate'] && config('cache_static'))
		{
			$data = url_parse($x['original_uri']);
			if(!empty($data['root']))
			{
				unset($_SERVER['HTTP_HOST'], $_SERVER['DOCUMENT_ROOT']);
				$_SERVER['HTTP_HOST'] = $data['host'];
				$_SERVER['DOCUMENT_ROOT'] = $data['root'];
			}

			if($obj)
			{
				$obj->set_attr('static_recreate_data', $x);
				bors_object_create($obj);
			}
			else
				debug_hidden_log('static-cache', "Can't load recreateable object {$x['class_id']}({$x['object_id']}, url={$x['original_uri']}, file={$x['file']}");
			echo "Recreated";
		}
		else
		{
			@unlink($x['file']);
			if(file_exists($x['file']))
			{
				debug_hidden_log('static-cache', "Can't delete file {$x['class_id']}({$x['object_id']}, url={$x['original_uri']}, file={$x['file']}");
				echo "Can't delete";
			}
			else
			{
				echo 'Deleted';
				@rmdir(dirname($x['file']));
				@rmdir(dirname(dirname($x['file'])));
				@rmdir(dirname(dirname(dirname($x['file']))));
			}
		}

		echo "<br/>\n";
	}

	bors_thread_unlock('statfile-cache-clean');
	echo "In ".(time()-$start)." sec<br/>\n";
