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

try
{

	$db = new driver_mysql(config('cache_database'));

	foreach(bors_each('cache_static', array("expire_time BETWEEN 0 AND ".time())) as $x)
	{
		echo "{$x->original_uri()}, {$x->id()} [recreate={$x->recreate()}]: ";

		$obj = $x->target();

		if($x->recreate() && config('cache_static'))
		{
			$data = url_parse($x->original_uri());
			if(!empty($data['root']))
			{
				unset($_SERVER['HTTP_HOST'], $_SERVER['DOCUMENT_ROOT']);
				$_SERVER['HTTP_HOST'] = $data['host'];
				$_SERVER['DOCUMENT_ROOT'] = $data['root'];
			}

			if($obj)
			{
				$obj->set_attr('static_recreate_object', $x);
				bors_object_create($obj);
			}
			else
				debug_hidden_log('static-cache', "Can't load recreateable object {$x->target_class_id()}({$x->target_id()}), url={$x->original_uri()}, file={$x->id()}");
			echo "Recreated";
		}
		else
		{
			if($fx = object_property($x, 'file'))
				@unlink($fx);

			if(file_exists($x->id()))
			{
				debug_hidden_log('static-cache', "Can't delete file {$x->target_class_id()}({$x->target_id()}), url={$x->original_uri()}, file={$x->id()}");
				echo "Can't delete";
			}
			else
			{
				echo 'Deleted';
				@rmdir(dirname($x->id()));
				@rmdir(dirname(dirname($x->id())));
				@rmdir(dirname(dirname(dirname($x->id()))));
			}

			$x->delete();
		}

		echo "<br/>\n";
	}

}
catch(Exception $e)
{
	debug_hidden_log('exception-static', "Exception: ".bors_lib_exception::catch_trace($e));
}

bors_thread_unlock('statfile-cache-clean');
echo "In ".(time()-$start)." sec<br/>\n";
