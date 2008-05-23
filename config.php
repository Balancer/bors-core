<?php

if(!defined("BORS_CORE"))
	define("BORS_CORE", '/var/www/.bors/bors-core');

if(!defined("BORS_LOCAL"))
	define("BORS_LOCAL", '/var/www/.bors/bors-local');

if(!defined("BORS_HOST"))
	define("BORS_HOST", $_SERVER['DOCUMENT_ROOT'].'/../.bors-host');

if(!defined('BORS_3RD_PARTY'))
	define('BORS_3RD_PARTY', dirname(__FILE__).'/../bors-third-party');

function config_set($key, $value) { $GLOBALS['cms']['config'][$key] = $value; }
function config($key, $def = NULL) { return isset($GLOBALS['cms']['config'][$key]) ? $GLOBALS['cms']['config'][$key] : $def; }

function config_seth($section, $hash, $key, $value) { $GLOBALS['cms']['config'][$section][$hash][$key] = $value; }
function configh($section, $hash, $key, $def = NULL) { return isset($GLOBALS['cms']['config'][$section][$hash][$key]) ? $GLOBALS['cms']['config'][$section][$hash][$key] : $def; }

function mysql_access($db = 'BORS', $login = NULL, $password = NULL, $host='localhost')
{
	$GLOBALS['cms']['mysql'][$db]['login'] = $login;
	$GLOBALS['cms']['mysql'][$db]['password'] = $password;
}

//config_set('debug_mysql_queries_log', '/var/www/balancer.ru/htdocs/logs/sql-timig.log');

if(file_exists(BORS_LOCAL.'/config-pre.php'))
	include_once(BORS_LOCAL.'/config-pre.php');

if(file_exists(BORS_HOST.'/config-pre.php'))
	include_once(BORS_HOST.'/config-pre.php');

require_once('config/default.php');

$host = @$_SERVER['HTTP_HOST'];

$includes = array(
	BORS_HOST,
	@BORS_CORE."/vhosts/{$host}",
	BORS_LOCAL,
	"{$_SERVER['DOCUMENT_ROOT']}/include",
	BORS_CORE,
	BORS_CORE.'/PEAR',
);

$includes[] = BORS_3RD_PARTY;

$delim = empty($_ENV['windir']) ? ":" : ";";
ini_set('include_path', ini_get('include_path') . $delim . join($delim, $includes));

require_once('classes/inc/BorsMemCache.php');
require_once('inc/debug.php');
require_once('inc/global-data.php');
require_once('inc/locales.php');
require_once('obsolete/DataBase.php');
require_once('obsolete/DataBaseHTS.php');
require_once('obsolete/cache/CacheStaticFile.php');

if(file_exists(BORS_CORE.'/config/local.php'))
	include_once(BORS_CORE.'/config/local.php');

if(file_exists(BORS_LOCAL.'/config.php'))
	include_once(BORS_LOCAL.'/config.php');

if(file_exists(BORS_HOST.'/config.php'))
	include_once(BORS_HOST.'/config.php');

$GLOBALS['now'] = time();

function bors_init()
{
	require_once('engines/bors.php');

	require_once('engines/lcml.php');
	require_once('inc/navigation.php');
	require_once('engines/bors/vhosts_loader.php');
	require_once('inc/locales.php');
	require_once('engines/bors/object_show.php');

	require_once('classes/Cache.php');
}

function bors_dirs()
{
	$vhost = '/vhosts/'.@$_SERVER['HTTP_HOST'];
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

bors_init();
