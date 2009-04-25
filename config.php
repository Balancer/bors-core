<?php

if(!defined("BORS_CORE"))
	define("BORS_CORE", '/var/www/.bors/bors-core');

if(!defined("BORS_LOCAL"))
	define("BORS_LOCAL", '/var/www/.bors/bors-local');

if(!defined("BORS_HOST"))
	define("BORS_HOST", $_SERVER['DOCUMENT_ROOT'].'/../.bors-host');

if(!defined('BORS_3RD_PARTY'))
	define('BORS_3RD_PARTY', dirname(__FILE__).'/../bors-third-party');

function config_set_ref($key, &$value) { $GLOBALS['cms']['config'][$key] = $value; }
function config_set($key, $value) { return $GLOBALS['cms']['config'][$key] = $value; }
function config($key, $def = NULL) { return isset($GLOBALS['cms']['config'][$key]) ? $GLOBALS['cms']['config'][$key] : $def; }

function config_seth($section, $hash, $key, $value) { return $GLOBALS['cms']['config'][$section][$hash][$key] = $value; }
function configh($section, $hash, $key, $def = NULL) { return isset($GLOBALS['cms']['config'][$section][$hash][$key]) ? $GLOBALS['cms']['config'][$section][$hash][$key] : $def; }

function mysql_access($db = 'BORS', $login = NULL, $password = NULL, $host='localhost')
{
	$GLOBALS['cms']['mysql'][$db]['login'] = $login;
	$GLOBALS['cms']['mysql'][$db]['password'] = $password;
	if($host && $host != 'localhost')
		$GLOBALS['cms']['mysql'][$db]['server'] = $host;
}

function config_mysql_login($db, $default = '') { return $res = @$GLOBALS['cms']['mysql'][$db]['login'] ? $res : $default; }
function config_mysql_password($db, $default = 'root') { return $res = @$GLOBALS['cms']['mysql'][$db]['password'] ? $res : $default; }
function config_mysql_server($db, $default = 'localhost') { return $res = @$GLOBALS['cms']['mysql'][$db]['server'] ? $res : $default; }

ini_set('session.use_trans_sid', false);
session_start();

if(file_exists(BORS_LOCAL.'/config-pre.php'))
	include_once(BORS_LOCAL.'/config-pre.php');

if(file_exists(BORS_HOST.'/config-pre.php'))
	include_once(BORS_HOST.'/config-pre.php');

require_once('config/default.php');
config_set('admin_config_class', 'bors_admin_config');
config_set('debug_hidden_log_dir', $_SERVER['DOCUMENT_ROOT'].'/logs');

config_set('image_transform_engine', 'GD');

$host = @$_SERVER['HTTP_HOST'];

$vhost = '/vhosts/'.@$_SERVER['HTTP_HOST'];
$includes = array(
	BORS_LOCAL.$vhost,
	BORS_LOCAL,
	BORS_HOST.$vhost,
	BORS_HOST,
	"{$_SERVER['DOCUMENT_ROOT']}/include",
	BORS_CORE,
	BORS_CORE.'/PEAR',
	BORS_3RD_PARTY,
	BORS_3RD_PARTY.'/PEAR',
);

if(defined('BORS_APPEND'))
	$includes = array_merge($includes, explode(' ', BORS_APPEND));

$delim = empty($_ENV['windir']) ? ":" : ";";
ini_set('include_path', ini_get('include_path') . $delim . join($delim, array_unique($includes)));

require_once('classes/inc/BorsMemCache.php');
require_once('inc/debug.php');
require_once('inc/global-data.php');
require_once('inc/locales.php');
require_once('inc/system.php');
require_once('inc/datetime.php');
require_once('obsolete/DataBase.php');
require_once('obsolete/DataBaseHTS.php');

if(file_exists(BORS_CORE.'/config/local.php'))
	include_once(BORS_CORE.'/config/local.php');

if(file_exists(BORS_LOCAL.'/config.php'))
	include_once(BORS_LOCAL.'/config.php');

if(file_exists(BORS_HOST.'/config.php'))
	include_once(BORS_HOST.'/config.php');

if(config('debug_can_change_now'))
{
	$GLOBALS['now'] = empty($_GET['now']) ? time() : intval(strtotime($_GET['now']));
	unset($_GET['now']);
}
else
	$GLOBALS['now'] = time();

$GLOBALS['mysql_now'] = date_format_mysqltime($GLOBALS['now']);

function bors_init()
{
	ini_set('default_charset', config('default_character_set'));
	setlocale(LC_ALL, config('locale'));

	if(config('memcached'))
	{
		$memcache = &new Memcache;
		$memcache->connect(config('memcached')) or debug_exit("Could not connect memcache");
		config_set_ref('memcached_instance', $memcache);
	}

	require_once('engines/bors.php');

	require_once('inc/navigation.php');
	require_once('engines/bors/vhosts_loader.php');
	require_once('engines/bors/users.php');
	require_once('inc/locales.php');
	require_once('inc/urls.php');
	require_once('engines/bors/object_show.php');

	require_once('classes/Cache.php');
}

function bors_dirs($host = NULL)
{
	if(!$host)
		$host = @$_SERVER['HTTP_HOST'];

	$vhost = '/vhosts/'.$host;

	$data = array();
	if(defined('BORS_APPEND'))
		$data = array_merge($data, explode(' ', BORS_APPEND));

	$data = array_merge($data, array(
		BORS_LOCAL.$vhost,
		BORS_LOCAL,
		BORS_HOST.$vhost,
		BORS_HOST,
		BORS_CORE,
	));

	return array_unique($data);
}

function bors_include_once($file, $warn = false)
{
	return bors_include($file, $warn, true);
}

function bors_include($file, $warn = false, $once = false)
{
	foreach(bors_dirs() as $dir)
	{
		if(file_exists($ff = $dir.'/'.$file))
		{
			if($once)
				require_once($ff);
			else
				require($ff);

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

if(get_magic_quotes_gpc() && $_POST)
	ungpc_array($_POST);

bors_init();
