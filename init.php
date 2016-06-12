<?php

if(empty($GLOBALS['stat']['start_microtime']))
	$GLOBALS['stat']['start_microtime'] = microtime(true);

//if(!empty($GLOBALS['b2_data']['inited_new']))
//{
//	throw new Exception("Load old init from new B2");
//}

/*
	Инициализация всех систем фреймворка.
	После вызова этого файла можно использовать любой функционал.
*/

if(!defined('COMPOSER_ROOT'))
	define('COMPOSER_ROOT', dirname(dirname(dirname(__DIR__))));

if(!defined('BORS_CORE'))
	define('BORS_CORE', __DIR__);

define('BORS_ROOT', dirname(BORS_CORE).DIRECTORY_SEPARATOR);

if(!defined('COMPOSER_INCLUDED'))
{
	if(!file_exists($cr = COMPOSER_ROOT.'/vendor/autoload.php'))
	{
		@header('HTTP/1.1 500 Internal Server Error');
		exit("Can't find Composer as $cr");
	}

	$GLOBALS['bors.composer.class_loader'] = require $cr;
	define('COMPOSER_INCLUDED', true);
}

if(!defined('BORS_EXT'))
	define('BORS_EXT', BORS_ROOT.'bors-ext');

if(!defined('BORS_LOCAL'))
	define('BORS_LOCAL', BORS_ROOT.'bors-local');

if(!defined('BORS_SITE'))
	define('BORS_SITE', dirname(@$_SERVER['DOCUMENT_ROOT']).DIRECTORY_SEPARATOR.'bors-site');

if(!defined('BORS_3RD_PARTY'))
{
	if(file_exists($d = dirname(COMPOSER_ROOT).'/bors-third-party'))
		define('BORS_3RD_PARTY', $d);
	elseif(file_exists($d = BORS_ROOT.'bors-third-party'))
		define('BORS_3RD_PARTY', $d);
	elseif(file_exists($d = '/var/www/repos/bors-third-party'))
		define('BORS_3RD_PARTY', $d);
	else
		define('BORS_3RD_PARTY', NULL);
}

if(!empty($_SERVER['HTTP_X_REAL_IP']) && @$_SERVER['REMOTE_ADDR'] == @$_SERVER['SERVER_ADDR'])
	$_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_X_REAL_IP'];

// Before configs — they may use bors_url_map and other.
bors_transitional::init();
bors_funcs::noop();

foreach(array(COMPOSER_ROOT, BORS_LOCAL, BORS_SITE) as $base_dir)
	if(file_exists($file = "{$base_dir}/config-pre.php"))
		include_once($file);

$dir = __DIR__;
bors_config_ini($dir.'/config.ini');
require_once($dir.'/config.php');

$GLOBALS['bors_data']['vhost_handlers'] = array();
//$GLOBALS['bors_map'] = array();

// Пока не убирать: Fatal error: Call to undefined function calling_function_name() in /var/www/bors/composer/vendor/balancer/bors-core/classes/bors/object/simple.php on line 288
require_once('inc/system.php');

$host = @$_SERVER['HTTP_HOST'];

$vhost = '/vhosts/'.@$_SERVER['HTTP_HOST'];
$includes = array(
	BORS_SITE,
	BORS_LOCAL.$vhost,
	BORS_LOCAL,
	BORS_EXT,
	BORS_CORE,
	BORS_CORE.'/PEAR',
	BORS_3RD_PARTY,
	BORS_3RD_PARTY.'/PEAR',
);

if(defined('BORS_APPEND'))
	$includes = array_merge($includes, explode(' ', BORS_APPEND));

$includes[] = COMPOSER_ROOT;

if(defined('INCLUDES_APPEND'))
	$includes = array_merge($includes, explode(' ', INCLUDES_APPEND));

if(file_exists(dirname(BORS_CORE).'/composer'))
	$includes[] = dirname(BORS_CORE);

if(file_exists(dirname(BORS_SITE).'/composer'))
	$includes[] = dirname(BORS_SITE);

ini_set('include_path', ini_get('include_path') . PATH_SEPARATOR . join(PATH_SEPARATOR, array_unique($includes)));

bors_function_include('locale/ec');

// Our replace for gettext if it not installed
if(!function_exists('_'))
{
	function _($text) { return ec($text); }
}

spl_autoload_register('class_include');

$dirs = [BORS_3RD_PARTY, BORS_EXT, BORS_LOCAL, BORS_SITE];

// foreach(bors::$package_app_path as $path)
//	$dirs[] = $path;

if(!empty($GLOBALS['B2']['main_app']))
	$dirs[] = $GLOBALS['B2']['main_app']->package_path();

$dirs[] = COMPOSER_ROOT;

foreach(array_reverse(array_unique(array_reverse($dirs))) as $dir)
{
	if(file_exists($dir.'/config.ini'))
		bors_config_ini($dir.'/config.ini');

	if(file_exists($dir.'/config.php'))
		require_once($dir.'/config.php');
}

if(!empty($GLOBALS['bors']['config']['config_hosts']))
	foreach($GLOBALS['bors']['config']['config_hosts'] as $config_file)
		require_once($config_file);

if(file_exists($f = COMPOSER_ROOT.'/config-host.php'))
	require_once($f);

if(!file_exists($d = config('cache_dir')));
	mkpath($d, 0750);

//	Инициализация Stash-кеша с автоопределением типа движка, если в локальном конфиге не было задано заранее
if(!config('cache.stash.pool') && class_exists('Stash\Pool'))
{
	$pool = NULL;

	if(class_exists('Redis', false))
	{
		try
		{
			if(config('redis.servers'))
			{
				$servers = [];
				foreach(config('redis.servers') as $s)
					$servers[] = [$s['host'], $s['port']];
			}
			else
				$servers = [['server' => '127.0.0.1', 'port' => 6379, 'ttl' => 86400]];

			$driver = new Stash\Driver\Redis(['servers' => $servers]);

			$pool = new Stash\Pool($driver);
			$pool->getItem('foo')->get();
		}
		catch(Exception $e)
		{
			bors_debug::exception_log('warning-cache', "Can't load Stash Redis cache with config ".print_r(['servers' => $servers], true), $e);
			$pool = NULL; 
		}
	}

	//TODO: elseif() { ... } — добавить другие варианты Stash-драйверов
/*
			$driver = new Stash\Driver\FileSystem();
			$options = array('path' => '/tmp/stash-cache/');
			$driver->setOptions($options);
*/

	if($pool)
		config_set('cache.stash.pool', $pool);
}

if(config('debug_can_change_now'))
{
	$GLOBALS['now'] = empty($_GET['now']) ? time() : intval(strtotime($_GET['now']));
	unset($_GET['now']);
}
else
	$GLOBALS['now'] = time();

bors_function_include('time/date_format_mysqltime');
$GLOBALS['mysql_now'] = date_format_mysqltime($GLOBALS['now']);

// After configs. In url_map may be used config() data.
bors::init_new();


/**
 * Инициализация ядра системы
*/
function bors_init()
{
	if(config('debug.execute_trace'))
	{
		bors_function_include('debug/execute_trace');
		debug_execute_trace("bors_init() begin: ".@$GLOBALS['bors_full_request_url']);
		debug_execute_trace("\tBORS_CORE=".BORS_CORE."; BORS_SITE=".BORS_SITE);
	}

	if(config('internal_charset'))
		ini_set('default_charset', config('internal_charset'));
	else
		config_set('internal_charset', ini_get('default_charset'));

	if(config('locale'))
		setlocale(LC_ALL, config('locale'));

	if(config('memcached'))
	{
		$memcache = new Memcache;

		if(!@$memcache->connect(config('memcached')))
			bors_debug::syslog("memcache-error", "Can't connect");

		config_set('memcached_instance', $memcache);
	}

	require_once('engines/bors.php');

	require_once(BORS_CORE.'/inc/navigation.php');
	require_once(BORS_CORE.'/engines/bors/vhosts_loader.php');
	require_once(BORS_CORE.'/engines/bors/users.php');
	require_once(BORS_CORE.'/inc/locales.php');
	require_once(BORS_CORE.'/inc/urls.php');
	require_once(BORS_CORE.'/engines/bors/object_show.php');

	require_once('classes/Cache.php');

	if(config('debug.execute_trace'))
		debug_execute_trace('bors_init() done.');
}

if(get_magic_quotes_gpc() && $_POST)
	ungpc_array($_POST);

bors_init();
register_shutdown_function('bors_exit_handler');
//stream_wrapper_register('xfile', 'bors_wrappers_xfile') or die('Failed to register protocol xfile');

bors_function_include('client/bors_client_analyze');
bors_client_analyze();

if(file_exists(BORS_EXT.'/config-post.php'))
	include_once(BORS_EXT.'/config-post.php');

if(file_exists(BORS_LOCAL.'/config-post.php'))
	include_once(BORS_LOCAL.'/config-post.php');

if(file_exists(BORS_SITE.'/config-post.php'))
	include_once(BORS_SITE.'/config-post.php');

if(file_exists(BORS_HOST.'/config-post.php'))
	include_once(BORS_HOST.'/config-post.php');


/**
	=================================================
	Функции первой необходимости, нужные для загрузки
	системы автокеширования функций
	=================================================
*/

function register_project($project_name, $project_path)
{
	$GLOBALS['bors_data']['projects'][$project_name] = array(
		'project_path' => $project_path,
	);
}

function register_router($base_url, $base_class)
{
	if(preg_match('/^(\w+?)_(\w+)$/', $base_class, $m))
		list($project, $sub) = array($m[1], $m[2]);

	$path = @$GLOBALS['bors_data']['projects'][$project]['project_path'];

	if(file_exists($r = "$path/classes/".str_replace('_', '/', $base_class)."/routes.php"))
	{
		$GLOBALS['bors_context']['base_url'] = $base_url;
		$GLOBALS['bors_context']['base_class'] = $base_class;
		require $r;
		unset($GLOBALS['bors_context']['base_url']);
		unset($GLOBALS['bors_context']['base_class']);
	}

	$GLOBALS['bors_data']['routers'][$base_url] = array(
		'base_class' => $base_class,
	);
}

// http://admin.aviaport.wrk.ru/projects/maks2013/
function bors_route($map)
{
	$base_url = $GLOBALS['bors_context']['base_url'];
	$base_class = $GLOBALS['bors_context']['base_class'];

	foreach($map as $x)
	{
		if(preg_match('!^(\S+)\s*=>\s*(_\S+)$!', trim($x), $m))
			$GLOBALS['bors_map'][] = $base_url.$m[1].' => '.$base_class.$m[2];
//		var_dump($GLOBALS['bors_map']);
	}
}

bors_transitional::init();

function bors_vhost_routes($host, $routes)
{
	global $bors_data;
	$bors_data['vhosts'][$host]['bors_map'] = $routes;
}

function set_bors_project($project_name)
{
	$GLOBALS['bors_current_project'] = $project_name;
}

function bors_current_project()
{
	return $GLOBALS['bors_current_project'];
}

/**
	Сгенерировать автоматический класс, подключаемый к $url
	$attrs — атрибуты класса
	$project — проект, к которому привязывается класс. Если не указано, то текущий проект
	bors_auto_class('directory_airline', 'search', 'bors_meta_search')
*/

function bors_auto_class($item_name, $action_name, $base_class_name, $attrs = array())
{
	$project = bors_current_project();
	$new_class_name = "{$project}_{$item_name}_{$action_name}";
	$funcs = array();
	foreach($attrs as $property => $value)
		$funcs[] = "function _{$property}_def() { return $value; }";
	$code = "class {$new_class_name} extends {$base_class_name} { ".join("\n", $funcs)." }";
	eval($code);
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
		return bors_throw($message);

	echo $message;
}

function bors_function_include($req_name)
{
	static $defined = array();

	if(preg_match('!^(\w+)/(\w+)$!', $req_name, $m))
	{
		$path = $m[1];
		$name = $m[2];
	}
	else
	{
		$path = '';
		$name = $req_name;
	}

	if(!empty($defined[$req_name]))
		return;

	$defined[$req_name] = true;

	return require_once(BORS_CORE.'/inc/functions/'.$req_name.'.php');
}

/**
 * @param string $file
 * Загрузить .ini файл в параметры конфигурации.
 */
function bors_config_ini($file)
{
	$ini_data = parse_ini_file($file, true);

	if($ini_data === false)
		bors_throw("'$file' parse error");

	foreach($ini_data as $section_name => $data)
	{
		if($section_name == 'global' || $section_name == 'config')
			$GLOBALS['cms']['config'] = array_merge($GLOBALS['cms']['config'], $data);
		else
			foreach($data as $key => $value)
				$GLOBALS['cms']['config'][$section_name.'.'.$key] = $value;
	}
}

function bors_use_mysql()
{
	bors_function_include('time/date_format_mysqltime');
	$GLOBALS['mysql_now'] = date_format_mysqltime($GLOBALS['now']);
}

function bors_use_debug()
{
	require_once('inc/debug.php');
}

function bors_exec_time()
{
	return microtime(true) - $GLOBALS['stat']['start_microtime'];
}
