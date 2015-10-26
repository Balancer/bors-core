<?php

/*
	Подключать по bors_funcs::noop();
*/

function class_include($class_name, &$args = array()) { return bors_class_loader::load_file($class_name, $args); }

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

	if(defined('BORS_EXT') && is_dir(BORS_EXT))
		$data[] = BORS_EXT;

	$data[] = dirname(__DIR__); // BORS_CORE

	if(defined('BORS_3RD_PARTY') && is_dir(BORS_3RD_PARTY))
		$data[] = BORS_3RD_PARTY;

	if(!empty($GLOBALS['bors_data']['projects']))
		foreach($GLOBALS['bors_data']['projects'] as $project_name => $x)
			$data[] = $x['project_path'];

	return $dirs[$skip_config][$host] = array_unique(array_filter($data));
}

if(empty($GLOBALS['cms']) || empty($GLOBALS['cms']['config']))
	$GLOBALS['cms']['config'] = array();

function config_set_ref($key, &$value) { $GLOBALS['cms']['config'][$key] = $value; }

if(!function_exists('config_set'))
{
	function config_set($key, $value) { return $GLOBALS['cms']['config'][$key] = $value; }
}

if(!function_exists('config'))
{
	// Не максировать через @!
	function config($key, $def = NULL)
	{
		if(array_key_exists($key, $GLOBALS['cms']['config']))
			return  $GLOBALS['cms']['config'][$key];

		return $def;
	}
}

function config_seth($section, $hash, $key, $value) { return $GLOBALS['cms']['config'][$section][$hash][$key] = $value; }

// Не максировать через @!
function configh($section, $hash, $key, $def = NULL)
{
	return !empty($GLOBALS['cms']['config'][$section][$hash]) && array_key_exists($key, $GLOBALS['cms']['config'][$section][$hash])
		? $GLOBALS['cms']['config'][$section][$hash][$key]
		: $def;
}

if(function_exists('mb_strtolower') && strtolower(ini_get('default_charset')) == 'utf-8')
{
	// Фиг его знает, с чего PHP без этого перестал работать в консоли
	// Маскируем, ибо DEPRECATED
	@ini_set('mbstring.internal_encoding', 'UTF-8');
	function bors_upper($str) { return mb_strtoupper($str); }
	function bors_lower($str) { return mb_strtolower($str); }
	function bors_strlen($str) { return mb_strlen($str); }
	function bors_substr($str, $start, $length=NULL) { return is_null($length) ? mb_substr($str, $start) : mb_substr($str, $start, $length); }
	function bors_strpos($str, $need, $start=NULL) { return is_null($start) ? mb_strpos($str, $need) : mb_strpos($str, $need, $start); }
	function bors_strrpos($str, $need, $start=NULL) { return is_null($start) ? mb_strrpos($str, $need) : mb_strrpos($str, $need, $start); }
	function bors_stripos($str, $need, $start=NULL) { return is_null($start) ? mb_stripos($str, $need) : mb_stripos($str, $need, $start); }
	function bors_ucfirst($str) { return mb_substr(mb_strtoupper($str), 0, 1).mb_substr(mb_strtolower($str), 1); }
}
else
{
	function bors_lower($str) { return strtolower($str); }
	function bors_upper($str) { return strtoupper($str); }
	function bors_strlen($str) { return strlen($str); }
	function bors_substr($str, $start, $length=NULL) { return is_null($length) ? substr($str, $start) : substr($str, $start, $length); }
	function bors_strpos($str, $need, $start=NULL) { return is_null($start) ? strpos($str, $need) : strpos($str, $need, $start); }
	function bors_strrpos($str, $need, $start=NULL) { return is_null($start) ? strrpos($str, $need) : strrpos($str, $need, $start); }
	function bors_stripos($str, $need, $start=NULL) { return is_null($start) ? stripos($str, $need) : stripos($str, $need, $start); }
	function bors_ucfirst($str) { return ucfirst($str); }
}

eval('class bors_log  extends '.config('log.class', 'bors_log_stub').' { } ');
