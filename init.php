<?php

/*
	Инициализация всех систем фреймворка.
	После вызова этого файла можно использовать любой функционал.
*/

if(!defined('BORS_CORE'))
	define('BORS_CORE', dirname(__FILE__));

if(!defined('BORS_LOCAL'))
	define('BORS_LOCAL', dirname(BORS_CORE).'/bors-local');

if(!defined('BORS_HOST'))
	define('BORS_HOST', realpath(@$_SERVER['DOCUMENT_ROOT'].'/../bors-host'));

if(!defined('BORS_SITE'))
	define('BORS_SITE', realpath(@$_SERVER['DOCUMENT_ROOT'].'/../bors-site'));

if(!defined('BORS_3RD_PARTY'))
	define('BORS_3RD_PARTY', dirname(BORS_CORE).'/bors-third-party');

/**
 * Извлекает поле $name из массива $data, если оно есть.
 * В противном случае возвращает $default. 
 * Если установлено $must_be_set, то при отсутствии
 * соответствующего элемента массива он создаётся в нём.
 * @param array $data 
 * @param string $name
 * @param mixed $default
 * @param bool $must_be_set
 * @return mixed
 */
function defval(&$data, $name, $default=NULL, $must_be_set = false)
{
	if($data && array_key_exists($name, $data))
		return $data[$name];

	if($must_be_set)
		$data[$name] = $default;

	return $default;
}

/**
 * Аналогично defval(), но удаляет из массива данных извлечённое значение
 * @param array $data
 * @param string $name
 * @param mixed $default
 * @return mixed
 */
function popval(&$data, $name, $default=NULL)
{
	if(!$data || !array_key_exists($name, $data))
		return $default;

	$ret = $data[$name];
	unset($data[$name]);
	return $ret;
}

/**
 * Аналогично defval(), но читается только непустое значение.
 * @param array $data
 * @param string $name
 * @param mixed $default
 * @return mixed
 */
function defval_ne(&$data, $name, $default=NULL)
{
	if(!empty($data[$name]))
		return $data[$name];

	return $default;
}

if(!empty($_SERVER['HTTP_X_REAL_IP']) && @$_SERVER['REMOTE_ADDR'] == @$_SERVER['SERVER_ADDR'])
	$_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_X_REAL_IP'];

function nospace($str) { return str_replace(' ', '', $str); }

function config_set_ref($key, &$value) { $GLOBALS['cms']['config'][$key] = $value; }
function config_set($key, $value) { return $GLOBALS['cms']['config'][$key] = $value; }
function config($key, $def = NULL) { return array_key_exists($key, $GLOBALS['cms']['config']) ? $GLOBALS['cms']['config'][$key] : $def; }

function config_seth($section, $hash, $key, $value) { return $GLOBALS['cms']['config'][$section][$hash][$key] = $value; }
function configh($section, $hash, $key, $def = NULL) { return array_key_exists($key, @$GLOBALS['cms']['config'][$section][$hash]) ? $GLOBALS['cms']['config'][$section][$hash][$key] : $def; }

function mysql_access($db, $login = NULL, $password = NULL, $host='localhost')
{
	if(preg_match('/^(\w+)=>(\w+)$/', nospace($db), $m))
	{
		$db = $m[1];
		$db_real = $m[2];
	}
	else
		$db_real = $db;

	$GLOBALS["_bors_conf_mysql_{$db}_db_real"] = $db_real;
	$GLOBALS["_bors_conf_mysql_{$db}_login"]   = $login;
	$GLOBALS["_bors_conf_mysql_{$db}_password"]= $password;
	$GLOBALS["_bors_conf_mysql_{$db}_server"]  = $host;
}

function config_mysql($param_name, $db) { return @$GLOBALS["_bors_conf_mysql_{$db}_{$param_name}"]; }

foreach(array(BORS_LOCAL, BORS_HOST, BORS_SITE) as $base_dir)
	if(file_exists($file = "{$base_dir}/config-pre.php"))
		include_once($file);

require_once(dirname(__FILE__).'/config.php');

if(config('system.use_sessions'))
{
	ini_set('session.use_trans_sid', false);
	@session_start();
}

$host = @$_SERVER['HTTP_HOST'];

$vhost = '/vhosts/'.@$_SERVER['HTTP_HOST'];
$includes = array(
	BORS_SITE,
	BORS_HOST,
	BORS_LOCAL.$vhost,
	BORS_LOCAL,
	BORS_CORE,
	BORS_CORE.'/PEAR',
	BORS_3RD_PARTY,
	BORS_3RD_PARTY.'/PEAR',
);

if(defined('BORS_APPEND'))
	$includes = array_merge($includes, explode(' ', BORS_APPEND));

if(defined('INCLUDES_APPEND'))
	$includes = array_merge($includes, explode(' ', INCLUDES_APPEND));

if(defined('INCLUDES_APPEND'))
	$includes = array_merge($includes, explode(' ', INCLUDES_APPEND));

ini_set('include_path', ini_get('include_path') . PATH_SEPARATOR . join(PATH_SEPARATOR, array_unique($includes)));

require_once('inc/debug.php');
require_once('classes/inc/BorsMemCache.php');
require_once('inc/global-data.php');
require_once('inc/locales.php');
require_once('inc/system.php');
require_once('inc/datetime.php');
require_once('inc/clients.php');
require_once('engines/bors.php');
require_once('engines/bors/vhosts_loader.php');

if(file_exists(BORS_LOCAL.'/config.php'))
	include_once(BORS_LOCAL.'/config.php');

if(file_exists(BORS_HOST.'/config.php'))
	include_once(BORS_HOST.'/config.php');

if(file_exists(BORS_SITE.'/config.php'))
	include_once(BORS_SITE.'/config.php');

if(config('debug_can_change_now'))
{
	$GLOBALS['now'] = empty($_GET['now']) ? time() : intval(strtotime($_GET['now']));
	unset($_GET['now']);
}
else
	$GLOBALS['now'] = time();

$GLOBALS['mysql_now'] = date_format_mysqltime($GLOBALS['now']);

/**
 * Инициализация ядра системы
*/
function bors_init()
{
	if(config('internal_charset'))
		ini_set('default_charset', config('internal_charset'));
	else
		config_set('internal_charset', ini_get('default_charset'));

	if(config('locale'))
		setlocale(LC_ALL, config('locale'));

	require_once('engines/bors/generated.php');

	if(config('memcached'))
	{
		$memcache = new Memcache;
		$memcache->connect(config('memcached')) or debug_exit("Could not connect memcache");
		config_set('memcached_instance', $memcache);
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

function bors_dirs($skip_config = false, $host = NULL)
{
	static $dirs = NULL;

	if(isset($dirs[$skip_config]))
		return $dirs[$skip_config];

	if(!$host)
		$host = @$_SERVER['HTTP_HOST'];

	$vhost = '/vhosts/'.$host;

	$data = array();
	if(!$skip_config && defined('BORS_APPEND'))
		$data = array_merge($data, explode(' ', BORS_APPEND));

	foreach(array(
		BORS_HOST,
		BORS_SITE,
		BORS_LOCAL.$vhost,
		BORS_LOCAL,
		BORS_CORE,
		BORS_3RD_PARTY,
	) as $dir)
		if(is_dir($dir))
			$data[] = $dir;

	return $dirs[$skip_config] = array_unique(array_filter($data));
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

	$message = "Can't bors_include({$file})";

	if($warn == 2)
		return bors_exit($message);

	echo $message;
}

if(get_magic_quotes_gpc() && $_POST)
	ungpc_array($_POST);

bors_init();
register_shutdown_function('bors_exit');
bors_client_analyze();
