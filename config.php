<?php

function config_set($key, $value) { $GLOBALS['cms']['config'][$key] = $value; }
function config($key) { return @$GLOBALS['cms']['config'][$key]; }

ini_set('include_path', ini_get('include_path') .':'. BORS_LOCAL .':'. BORS_HOST .':'. BORS_CORE);

require_once('classes/inc/MemCache.php');
require_once('inc/debug.php');
require_once('config/default.php');
@include_once('config/local.php');

function bors_init()
{
//	require_once('funcs/templates/global.php');
//	require_once('funcs/users.php');
//	require_once('funcs/navigation/go.php');
//	require_once('funcs/lcml.php');
//  require_once('include/classes/cache/CacheStaticFile.php');

	require_once('engines/bors.php');
	require_once('engines/bors/vhosts_loader.php');
	require_once('inc/locales.php');
	require_once('engines/bors/object_show.php');
}

function bors_dirs()
{
	$vhost = '/vhosts/'.$_SERVER['HTTP_HOST'];
	return array(BORS_HOST, BORS_LOCAL.$vhost, BORS_LOCAL, BORS_CORE.$vhost, BORS_CORE);
}

function bors_include($file, $warn = false)
{
	foreach(bors_dirs() as $dir)
	{
		if(file_exists($ff = $dir.'/'.$file))
		{
			require_once($ff);
			return;
		}
	}
	
	if(!$warn)
		return;
	
	$message = "Can't load {$file}";
	
	if($warn == 2)
		return bors_exit($message);
	
	echo $message;
}
