<?php
$GLOBALS['now'] = time();

if(!defined("BORS_INCLUDE"))
	define("BORS_INCLUDE", $_SERVER['DOCUMENT_ROOT']."/cms/");

if(!defined("BORS_HOST"))
	define("BORS_HOST", @BORS_INCLUDE_LOCAL);

if(!defined("BORS_LOCAL"))
	define("BORS_LOCAL", BORS_INCLUDE);

if(!defined("BORS_CORE"))
	define("BORS_CORE", BORS_INCLUDE);

include_once(BORS_INCLUDE.'config/default.php');

if(file_exists(BORS_INCLUDE.'config/local.php'))
	include_once(BORS_INCLUDE.'config/local.php');

if(file_exists(@BORS_INCLUDE_LOCAL.'config.php'))
	include_once(@BORS_INCLUDE_LOCAL.'config.php');

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
	return array_unique(array(BORS_HOST, BORS_LOCAL.$vhost, BORS_LOCAL, BORS_CORE.$vhost, BORS_CORE));
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
