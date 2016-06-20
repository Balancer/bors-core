---[ Statcache files clean ]---
<?php
$start = time();

require_once __DIR__.'/../config.php';

if(!config('debug_hidden_log_dir') || !file_exists(config('debug_hidden_log_dir')))
	exit("Empty hidden log dir: '".config('debug_hidden_log_dir')."'");

require_once BORS_CORE.'/inc/processes.php';

bors_function_include('debug/execute_trace');

// file_put_contents('/tmp/clean-trace-'.@$_SERVER['USER'], 'Go! '.print_r($_SERVER, true), FILE_APPEND);

// bors_thread_unlock('statfile-cache-clean');

//if(!bors_thread_lock('statfile-cache-clean', 600))
//	exit("Locked\n");

if(!config('cache_database'))
{
	bors_thread_unlock('statfile-cache-clean');
	exit("no db configure\n");
}

config_set('do_not_exit', true);


try
{
	echo date("r\n");

	// BETWEEN 0 AND NOW — чтобы не стирать -1.

	foreach(bors_each('cache_static', array("expire_time BETWEEN 0 AND ".time(), 'order' => 'RAND()', 'limit' => 1000)) as $x)
	{
//		echo "{$x->original_uri()}, {$x->id()} [rcr={$x->recreate()}]: ";
		echo "{$x->original_uri()} [rcr={$x->recreate()}]: ";

		$obj = $x->target();

		$_SERVER['DEBUG_STATCACHE_CLEAN'] = $x->debug_title();
		unset($_SERVER['DEBUG_STATCACHE_CLEAN_TARGET']);

		if($obj)
			$_SERVER['DEBUG_STATCACHE_CLEAN_TARGET'] = $obj->debug_title();

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
//				echo "Try recreate {$obj->debug_title()}\n";
				$obj->set_attr('static_recreate_object', $x);
//				config_set('debug.execute_trace', true);
				bors_object_create($obj);
//				echo "\t\tok\n";
			}
			else
//				bors_debug::syslog('static-cache', "Can't load recreateable object {$x->target_class_id()}({$x->target_id()}), url={$x->original_uri()}, file={$x->id()}");
			echo "Recreated";

		}
		else
		{
//			bors_debug::syslog('static-clean-unlink3', "{$x->id()}", false);
			@unlink($x->id());

			if(file_exists($x->id()))
			{
				bors_debug::syslog('static-cache-error', "Can't delete file {$x->target_class_id()}({$x->target_id()}), url={$x->original_uri()}, file={$x->id()}");
				echo "Can't delete";
			}
			else
			{
				echo 'Deleted';
				bors_lib_dir::clean_path(dirname($x->id()));
				$x->delete();
			}
		}

		echo "\n";

		bors_global::ping(1000);
	}

}
catch(Exception $e)
{
	bors_debug::syslog('static-clean-exception', "Exception: ".bors_lib_exception::catch_trace($e));
}

bors_thread_unlock('statfile-cache-clean');
echo "In ".(time()-$start)." sec<br/>\n";
