---[ Statcache files clean ]---
<?php
$start = time();

require_once('../config.php');
require_once(BORS_CORE.'/init.php');

require_once('obsolete/DataBase.php');
require_once('inc/processes.php');

file_put_contents('/tmp/clean-trace-'.$_SERVER['USER'], 'Go! '.print_r($_SERVER, true), FILE_APPEND);

if(!bors_thread_lock('statfile-cache-clean', 600))
	exit("Locked\n");

if(!config('cache_database'))
{
	bors_thread_unlock('statfile-cache-clean');
	exit("");
}

try
{

	$db = new driver_mysql(config('cache_database'));

	// BETWEEN 0 AND NOW — чтобы не стирать -1.
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
//				debug_hidden_log('static-cache', "Can't load recreateable object {$x->target_class_id()}({$x->target_id()}), url={$x->original_uri()}, file={$x->id()}");
			echo "Recreated";
		}
		else
		{
//			debug_hidden_log('static-clean-unlink3', "{$x->id()}", false);
			@unlink($x->id());

			if(file_exists($x->id()))
			{
				debug_hidden_log('static-cache', "Can't delete file {$x->target_class_id()}({$x->target_id()}), url={$x->original_uri()}, file={$x->id()}");
				echo "Can't delete";
			}
			else
			{
				echo 'Deleted';
				bors_lib_dir::clean_path(dirname($x->id()));
				$x->delete();
			}
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
