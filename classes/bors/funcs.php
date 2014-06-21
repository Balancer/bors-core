<?php

class bors_funcs
{
	static function noop() { }

	static function init()
	{

		if(!defined('BORS_CORE'))
			define('BORS_CORE', dirname(dirname(__DIR__)));

		if(!defined('BORS_EXT'))
			define('BORS_EXT', dirname(BORS_CORE).'/bors-ext');

		if(!defined('BORS_SITE'))
		{
			if(empty($_SERVER['HTTP_HOST']))
				define('BORS_SITE', '/var/www/'.$_SERVER['HOME']);
			else
				define('BORS_SITE', '/var/www/'.$_SERVER['HTTP_HOST']);
		}

//		require BORS_CORE.'/classes/bors/class/loader.php';
		require_once(__DIR__.'/../../engines/bors/generated.php');
	}
}

function class_include($class_name, &$args = array()) { return bors_class_loader::load($class_name, $args); }

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

function bors_dirs($skip_config = false, $host = NULL)
{
	static $dirs = NULL;

	if(!$host)
		$host = @$_SERVER['HTTP_HOST'];

	if(isset($dirs[$skip_config][$host]))
		return $dirs[$skip_config][$host];

	$vhost = '/vhosts/'.$host;

	$data = array();
	if(!$skip_config && defined('BORS_APPEND'))
		$data = array_merge($data, explode(' ', BORS_APPEND));

	if(defined('BORS_SITE') && is_dir(BORS_SITE))
		$data[] = BORS_SITE;

	if(defined('BORS_HOST') && is_dir(BORS_HOST))
		$data[] = BORS_HOST;

	if(defined('BORS_LOCAL'))
	{
		if(is_dir(BORS_LOCAL.$vhost))
			$data[] = BORS_LOCAL.$vhost;
		if(is_dir(BORS_LOCAL))
			$data[] = BORS_LOCAL;
	}

	if(is_dir(BORS_EXT))
		$data[] = BORS_EXT;

	$data[] = BORS_CORE;

	if(defined('BORS_3RD_PARTY') && is_dir(BORS_3RD_PARTY))
		$data[] = BORS_3RD_PARTY;

	if($prjs = @$GLOBALS['bors_data']['projects'])
		foreach($prjs as $project_name => $x)
			$data[] = $x['project_path'];

	if(defined('COMPOSER_ROOT'))
	{
		$lock = json_decode(file_get_contents(COMPOSER_ROOT . '/composer.lock'), true);
		foreach($lock['packages'] as $package)
		{
			$path = COMPOSER_ROOT . '/vendor/' . $package['name'];
			if(file_exists($path.'/classes') || file_exists($path.'/templates'))
				$data[] = $path;
		}
	}

	return $dirs[$skip_config][$host] = array_unique(array_filter($data));
}

function config_set_ref($key, &$value) { $GLOBALS['cms']['config'][$key] = $value; }
function config_set($key, $value) { return $GLOBALS['cms']['config'][$key] = $value; }
function config($key, $def = NULL) { return @array_key_exists($key, $GLOBALS['cms']['config']) ? $GLOBALS['cms']['config'][$key] : $def; }

function config_seth($section, $hash, $key, $value) { return $GLOBALS['cms']['config'][$section][$hash][$key] = $value; }
function configh($section, $hash, $key, $def = NULL) { return @array_key_exists($key, @$GLOBALS['cms']['config'][$section][$hash]) ? $GLOBALS['cms']['config'][$section][$hash][$key] : $def; }
