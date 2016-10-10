<?php

use B2\Cfg;

function ungpc_array(&$array)
{
	$result = array();

	foreach($array as $key => $val)
	{
		if(is_array($val))
			$result[$key] = ungpc_array($val);
		else
			$result[$key] = stripslashes($val);
	}
	return $array = $result;
}


	function get_new_global_id($db = NULL)
	{
		$db = new driver_mysql($db);
		$db->query("UPDATE hts_ext_system_data SET `value`=`value`+1 WHERE `key`='global_id';", false);
		return $db->get("SELECT `value` FROM hts_ext_system_data WHERE `key`='global_id';", false);
	}

	function new_id($engine, $local_id = NULL, $uri = NULL)
	{
		debug_exit("new_id($engine)");

		$db = new driver_mysql('HTS');

		$local_id	= $local_id	? ", local_id = ".intval($local_id) : "";
		$uri		= $uri 			? ", uri = ".addslashes($uri) : "";

		$db->query("INSERT INTO `global_ids` SET `engine` = '".addslashes($engine)."'$local_id$uri");
		$new_id = intval($db->last_id());

		if(!$new_id)
			exit("Ошибка получения global id");

		return $new_id;
	}

	function uri_by_global_id($id)
	{
		debug_exit("uri_by_global_id($id)");
		$db = new driver_mysql('HTS');

		return $db->get("SELECT uri FROM global_ids WHERE id = ".intval($id));
	}

	function global_id($engine, $local_id, $register=true)
	{
		debug_exit("global_id($engine)");
		$db = new driver_mysql('HTS');

		$id = $db->get("SELECT id FROM global_ids WHERE engine='".addslashes($engine)."' AND local_id = ".intval($local_id));

		if($id || !$register)
			return $id;

		return new_id($engine, $local_id);
	}

	function engine_id_by_global($id)
	{
		debug_exit("engine_id($id)");
		$db = new driver_mysql('HTS');

		$res = $db->get("SELECT engine, local_id FROM global_ids WHERE id = ".intval($id));
		return array($res['engine'], $res['local_id']);
	}

function __session_init($init = true)
{
	static $session_started = false;
	if($session_started || empty($_SERVER['HTTP_HOST']))
		return;

	if(Cfg::get('system.session.skip'))
		return;

	if(!$init && empty($_COOKIE['bors_session_init']))
		return;

	@SetCookie('bors_session_init',	true, ini_get('session.cookie_lifetime'), '/', @$_SERVER['HTTP_HOST']);
	ini_set('session.use_trans_sid', false);

	// via http://stackoverflow.com/a/22373561
	$sn = session_name();
	if (isset($_COOKIE[$sn]))
		$sessid = $_COOKIE[$sn];
	elseif (isset($_GET[$sn]))
		$sessid = $_GET[$sn];
	else
	{
		session_start();
		$session_started = true;
		return false;
	}

	if(!preg_match('/^[a-zA-Z0-9,\-]{22,40}$/', $sessid))
		return false;

	session_start();
	$session_started = true;
}

function session_var($name, $def = NULL, $set = false)
{
	if(Cfg::get('system.session.skip'))
		return $def;

	__session_init(false);

	if($set)
		return defvalset(@$_SESSION, $name, $def);

	return defval(@$_SESSION, $name, $def);
}

function pop_session_var($name, $def = NULL)
{
	$val = session_var($name, $def);
	unset($_SESSION[$name]);
	return $val;
}

function set_session_var($name, $value)
{
	if($value)
		__session_init(true);

	if($value)
		$_SESSION[$name] = $value;
	elseif(!empty($_SESSION))
		unset($_SESSION[$name]);

	return $value;
}

function session_array_append($name, $value)
{
	__session_init();
	$x = defval($_SESSION, $name, array());
	$x[] = $value;
	return $_SESSION[$name] = $x;
}

function set_session_message($message, $params = array())
{
	$type = defval($params, 'type', 'error');
	switch($type)
	{
		case 'success':
			set_session_var('success_message', $message);
			break;
		case 'notice':
			set_session_var('notice_message', $message);
			break;
		case 'error':
		default:
			set_session_var('error_message', $message);
			break;
	}

	if(($fields = @$params['error_fields']))
		set_session_var('error_fields', $fields);
}

function session_message($params = array())
{
	$type = defval($params, 'type', 'error');
	switch($type)
	{
		case 'success':
			return session_var('success_message');
		case 'notice':
			return session_var('notice_message');
			break;
		case 'error':
		default:
			return session_var('error_message');
	}

	return NULL;
}

function add_session_message($message, $params = array())
{
	static $added = array();
	if(!empty($added[md5($message)]))
		return NULL;

	$added[md5($message)] = true;

	if(($prev_msg = session_message($params)))
		$prev_msg .= "<br/>\n";

	return set_session_message($prev_msg . $message, $params);
}

function clean_all_session_vars()
{
	__session_init();
	if(!empty($_SESSION))
		foreach($_SESSION as $key => $value)
			unset($_SESSION[$key]);
}

function calling_function_name()
{
	$backtrace = debug_backtrace();
	$name = $backtrace[2]['function'];
	$backtrace = NULL;

	return $name;
}

function set_session_form_data($data)
{
	foreach($data as $field => $value)
		set_session_var("form_value_{$field}", $value);
}

function clear_session_form_data()
{
	foreach($_SESSION as $key => $value)
		if(preg_match('/^form_value_/', $key))
			set_session_var($key, NULL);
}
