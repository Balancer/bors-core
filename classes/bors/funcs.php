<?php

class bors_funcs
{
	static function init() { }
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
