<?php
	$_SERVER['HTTP_HOST'] = 'j4.forexpf.ru';
	$_SERVER['DOCUMENT_ROOT'] = '/var/www/html';

	define('BORS_CORE', dirname(dirname(__FILE__)));
	define('BORS_LOCAL', dirname(BORS_CORE).'/bors-local');

	include_once(BORS_CORE.'/config.php');

	include_once('obsolete/DataBase.php');

	$db = &new DataBase('CACHE');

	foreach($db->get_array("SELECT file, recreate, class_id, object_id, original_uri FROM cached_files WHERE expire_time BETWEEN 0 AND ".time()) as $x)
	{
		echo "{$x['file']}: ";
		$db->query("DELETE FROM cached_files WHERE file = '".addslashes($x['file'])."'");
		if($x['recreate'])
		{
			if($obj = object_load($x['original_uri']))
				bors_object_create($obj);
			elseif($obj = object_load($x['class_id'], $x['object_id']))
				bors_object_create($obj);
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
				echo 'Deleted';
		}
		
		echo "<br/>\n";
	}
