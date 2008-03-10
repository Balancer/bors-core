<?php

if(!defined("BORS_INCLUDE"))
	define("BORS_INCLUDE", $_SERVER['DOCUMENT_ROOT']."/cms/");

if(!defined("BORS_INCLUDE_LOCAL"))
	define("BORS_INCLUDE_LOCAL", "{$_SERVER['DOCUMENT_ROOT']}/cms-local/");

if(!defined("BORS_HOST"))
	define("BORS_HOST", @BORS_INCLUDE_LOCAL);

if(!defined("BORS_LOCAL"))
	define("BORS_LOCAL", BORS_INCLUDE);

if(!defined("BORS_CORE"))
	define("BORS_CORE", BORS_INCLUDE);

function config_set($key, $value) { $GLOBALS['cms']['config'][$key] = $value; }
function config($key) { return @$GLOBALS['cms']['config'][$key]; }

$includes = array(
	BORS_INCLUDE_LOCAL,
	BORS_INCLUDE."vhosts/{$_SERVER['HTTP_HOST']}",
	BORS_INCLUDE,
	"{$_SERVER['DOCUMENT_ROOT']}/include",
	BORS_INCLUDE.'PEAR'
);
$delim = empty($_ENV['windir']) ? ":" : ";";
ini_set('include_path', ini_get('include_path') . $delim . join($delim, $includes));

require_once('classes/inc/BorsMemCache.php');
require_once('inc/debug.php');
require_once('config/default.php');

if(file_exists(BORS_CORE.'/config/local.php'))
	include_once(BORS_CORE.'/config/local.php');

if(file_exists(BORS_LOCAL.'/config/local.php'))
	include_once(BORS_LOCAL.'/config/local.php');

if(file_exists(BORS_HOST.'config.php'))
	include_once(BORS_HOST.'config.php');

$GLOBALS['now'] = time();

function bors_init()
{
	require_once('engines/bors.php');
	require_once('funcs/navigation/go.php');
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

require_once('funcs/lcml.php');
require_once('include/classes/cache/CacheStaticFile.php');
