<?php

if(empty($GLOBALS['stat']['start_microtime']))
	$GLOBALS['stat']['start_microtime'] = microtime(true);

/*
	Инициализация всех систем фреймворка.
	После вызова этого файла можно использовать любой функционал.
*/

if(!defined('BORS_CORE'))
	define('BORS_CORE', dirname(__FILE__));

if(!defined('BORS_EXT'))
	define('BORS_EXT', dirname(BORS_CORE).DIRECTORY_SEPARATOR.'bors-ext');

if(!defined('BORS_LOCAL'))
	define('BORS_LOCAL', dirname(BORS_CORE).DIRECTORY_SEPARATOR.'bors-local');

if(!defined('BORS_HOST'))
	define('BORS_HOST', dirname(BORS_CORE).DIRECTORY_SEPARATOR.'bors-host');

if(!defined('BORS_SITE'))
	define('BORS_SITE', dirname(@$_SERVER['DOCUMENT_ROOT']).DIRECTORY_SEPARATOR.'bors-site');

if(!defined('BORS_3RD_PARTY'))
	define('BORS_3RD_PARTY', dirname(BORS_CORE).DIRECTORY_SEPARATOR.'bors-third-party');

if(!empty($_SERVER['HTTP_X_REAL_IP']) && @$_SERVER['REMOTE_ADDR'] == @$_SERVER['SERVER_ADDR'])
	$_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_X_REAL_IP'];

foreach(array(BORS_LOCAL, BORS_HOST, BORS_SITE) as $base_dir)
	if(file_exists($file = "{$base_dir}/config-pre.php"))
		include_once($file);

$dir = dirname(__FILE__);
bors_config_ini($dir.'/config.ini');
require_once($dir.'/config.php');

$GLOBALS['bors_data']['vhost_handlers'] = array();
$GLOBALS['bors_map'] = array();

if(!config('system.session.skip'))
{
	require_once('inc/system.php');
	__session_init();
}

$host = @$_SERVER['HTTP_HOST'];

$vhost = '/vhosts/'.@$_SERVER['HTTP_HOST'];
$includes = array(
	BORS_SITE,
	BORS_HOST,
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

if(defined('INCLUDES_APPEND'))
	$includes = array_merge($includes, explode(' ', INCLUDES_APPEND));

if(defined('INCLUDES_APPEND'))
	$includes = array_merge($includes, explode(' ', INCLUDES_APPEND));

ini_set('include_path', ini_get('include_path') . PATH_SEPARATOR . join(PATH_SEPARATOR, array_unique($includes)));

// Уникальный случай, грузим класс загрузчика вручную, так как
// автоматическую загрузку обеспечивает именно он сам
require BORS_CORE.'/classes/bors/class/loader.php';
function class_include($class_name, &$args = array()) { return bors_class_loader::load($class_name, $args); }

bors_function_include('locale/ec');

spl_autoload_register('class_include');

foreach(array(BORS_3RD_PARTY, BORS_EXT, BORS_LOCAL, BORS_HOST, BORS_SITE) as $dir)
{
	if(file_exists($dir.'/config.ini'))
		bors_config_ini($dir.'/config.ini');

	if(file_exists($dir.'/config.php'))
		include_once($dir.'/config.php');
}

if(!file_exists($d = config('cache_dir')));
	mkpath($d, 0750);

@chmod(dirname($d), 0777);

if(config('cache_code_monolith') && file_exists($php_cache_file = config('cache_dir') . '/functions.php'))
	require_once($php_cache_file);

if(config('debug_can_change_now'))
{
	$GLOBALS['now'] = empty($_GET['now']) ? time() : intval(strtotime($_GET['now']));
	unset($_GET['now']);
}
else
	$GLOBALS['now'] = time();

bors_function_include('time/date_format_mysqltime');
$GLOBALS['mysql_now'] = date_format_mysqltime($GLOBALS['now']);

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

	if(config('debug.execute_trace'))
		debug_execute_trace('bors_init() done.');
}

function bors_dirs($skip_config = false, $host = NULL)
{
	static $dirs = NULL;

	if(isset($dirs[$skip_config][$host]))
		return $dirs[$skip_config][$host];

	if(!$host)
		$host = @$_SERVER['HTTP_HOST'];

	$vhost = '/vhosts/'.$host;

	$data = array();
	if(!$skip_config && defined('BORS_APPEND'))
		$data = array_merge($data, explode(' ', BORS_APPEND));

	foreach(array(
		BORS_SITE,
		BORS_HOST,
		BORS_LOCAL.$vhost,
		BORS_LOCAL,
		BORS_EXT,
		BORS_CORE,
		BORS_3RD_PARTY,
	) as $dir)
		if(is_dir($dir))
			$data[] = $dir;

	return $dirs[$skip_config][$host] = array_unique(array_filter($data));
}

if(get_magic_quotes_gpc() && $_POST)
	ungpc_array($_POST);

bors_init();
register_shutdown_function('bors_exit');
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

function register_vhost($host, $documents_root=NULL, $bors_host=NULL)
{
	global $bors_data;

	if(empty($documents_root))
		$documents_root = '/var/www/'.$host.'/htdocs';

	if(empty($bors_host))
	{
		$bors_host = dirname($documents_root).'/bors-host';
		$bors_site = dirname($documents_root).'/bors-site';
	}
	else
		$bors_site = $bors_host;

	$map = array();

	if(file_exists($file = BORS_HOST.'/vhosts/'.$host.'/handlers/bors_map.php'))
		require_once($file);
	elseif(file_exists($file = BORS_LOCAL.'/vhosts/'.$host.'/handlers/bors_map.php'))
		require_once($file);
	elseif(file_exists($file = BORS_CORE.'/vhosts/'.$host.'/handlers/bors_map.php'))
		require_once($file);

	$map2 = $map;

	if(file_exists($file = $bors_site.'/handlers/bors_map.php'))
		require_once($file);

	if(file_exists($file = $bors_site.'/bors_map.php'))
		require_once($file);

	if(file_exists($file = $bors_site.'/url_map.php'))
		require_once($file);

	if(file_exists($file = $bors_host.'/handlers/bors_map.php'))
		require_once($file);

//	echo "$host: <xmp>"; print_r($map); echo "</xmp>";

	$bors_data['vhosts'][$host] = array(
		'bors_map' => array_merge($map2, $map),
		'bors_local' => $bors_host,
		'bors_site' => $bors_site,
		'document_root' => $documents_root,
	);
}

function bors_url_map($map_array)
{
	global $bors_map;
	$bors_map = array_merge($bors_map, $map_array);
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

function mkpath($strPath, $mode=0777)
{
    if(!$strPath || is_dir($strPath) || $strPath=='/')
        return true;

	if(!($pStrPath = dirname($strPath)))
		return true;

	if(!mkpath($pStrPath, $mode)) 
        return false;

	$err = @mkdir($strPath, $mode);
	@chmod($strPath, $mode);
	return $err;
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

/**
 * Извлекает поле $name из массива $data, если оно есть.
 * В противном случае возвращает $default.
 * @param array $data 
 * @param string $name
 * @param mixed $default
 * @return mixed
 */

function defval($data, $name, $default=NULL)
{
	if($data && array_key_exists($name, $data))
		return $data[$name];

	return $default;
}

/**
 * Работает как и defval(), но при отсутствии
 * соответствующего элемента массива он создаётся в нём.
 * @param array $data
 * @param string $name
 * @param mixed $default
 * @return mixed
 */
function defvalset(&$data, $name, $default=NULL)
{
	if($data && array_key_exists($name, $data))
		return $data[$name];

	return $data[$name] = $default;
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

/**
	Устновить элемент массива $name в переменную $value, если он до этого не определён
*/

function set_def(&$data, $name, $value)
{
	if($data && array_key_exists($name, $data))
		return $data[$name];

	return $data[$name] = $value;
}

function nospace($str) { return str_replace(' ', '', $str); }

function config_set_ref($key, &$value) { $GLOBALS['cms']['config'][$key] = $value; }
function config_set($key, $value) { return $GLOBALS['cms']['config'][$key] = $value; }
function config($key, $def = NULL) { return array_key_exists($key, $GLOBALS['cms']['config']) ? $GLOBALS['cms']['config'][$key] : $def; }

function config_seth($section, $hash, $key, $value) { return $GLOBALS['cms']['config'][$section][$hash][$key] = $value; }
function configh($section, $hash, $key, $def = NULL) { return @array_key_exists($key, @$GLOBALS['cms']['config'][$section][$hash]) ? $GLOBALS['cms']['config'][$section][$hash][$key] : $def; }

function mysql_access($db, $login = NULL, $password = NULL, $host='localhost')
{
	if(preg_match('/^(\w+)=>([\w\-]+)$/', nospace($db), $m))
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

	$file = BORS_CORE.'/inc/functions/'.$req_name.'.php';

	if(!config('cache_code_monolith'))
		// Если монолитное кеширование запрещено, то просто грузим файл и уходим
		return require_once($file);

	static $php_cache_file = NULL;
	if(!$php_cache_file)
		$php_cache_file = config('cache_dir') . '/functions.php';

	static $php_cache_content = NULL;

	if(!file_exists($php_cache_file))
		file_put_contents($php_cache_file, "<?php\n");

	if(!$php_cache_content)
		$php_cache_content = file_get_contents($php_cache_file);

	require_once($php_cache_file);

	if(function_exists($name))
		return;

	if(function_exists($path.'_'.$name))
		return;

	if(function_exists('bors_'.$path.'_'.$name))
		return;

	require_once($file);
	$function_code = file_get_contents($file);
	$function_code = "\n".trim(preg_replace('/^<\?php/', '', $function_code))."\n";
	$php_cache_content .= $function_code;
	$GLOBALS['bors_data']['php_cache_content'] = $php_cache_content;
}

/**
 * @param string $file
 * Загрузить .ini файл в параметры конфигурации.
 */
function bors_config_ini($file)
{
	foreach(parse_ini_file($file, true) as $section_name => $data)
	{
		if($section_name == 'global' || $section_name == 'config')
			$GLOBALS['cms']['config'] = array_merge($GLOBALS['cms']['config'], $data);
		else
			foreach($data as $key => $value)
				$GLOBALS['cms']['config'][$section_name.'.'.$key] = $value;
	}
}

function bors_use($uses)
{
	static $uses_active = array();
	foreach(explode(',', $uses) as $u)
	{
		$u = trim($u);

		if(in_array($u, $uses_active))
			continue;

		$uses_active[] = $u;

		if(preg_match('/\.css$/', $u))
		{
			if(preg_match('/^pre:(.+)$/', $u, $m))
				template_css($m[1], true);
			else
				template_css($u);

			continue;
		}

		if(preg_match('/\.js$/', $u))
		{
			if(preg_match('/^pre:(.+)$/', $u, $m))
				template_js_include($m[1], true);
			else
				template_js_include($u);

			continue;
		}

		if(preg_match('/^\w+$/', $u))
		{
			if(function_exists($f = "bors_use_{$u}"))
			{
				call_user_func($f);
				continue;
			}

//			На будущее.
//			if(file_exists($file = BORS_CORE.DIRECTORY_SEPARATOR.'uses'.DIRECTORY_SEPARATOR.$u.'.php'))
//			{
//				require_once($file);
//				continue;
//			}

			if(preg_match('/^(\w+?)_(\w+)$/', $u, $m))
			{
				bors_function_include("{$m[1]}/{$m[2]}");
				continue;
			}
		}

		if(preg_match('!^(\w+/\w+)$!', $u))
		{
			bors_function_include($u);
			continue;
		}

		bors_throw("Unknown bors_use('$u')");
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
