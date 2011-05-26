<?php

#require_once('bors/vhosts_loader.php');
require_once('bors/names.php');
require_once('bors/messages.php');
require_once('bors/objects_array.php');
require_once('bors/object_loader.php');
require_once('inc/bors/cross.php');
require_once('engines/smarty/global.php');

function object_load($class, $object_id=NULL, $args=array())
{
	if(is_numeric($class))
		$class = class_id_to_name($class);

	if(config('debug_trace_object_load'))
		debug_hidden_log('objects_load', "$class($object_id)", config('debug_trace_object_load_trace'));

	if(!$class)
		return;

	//TODO: сделать отброс пробельных символов
//	if(!is_object($object_id))
//		$object_id = trim($object_id, "\n\r ");

	if(is_null($object_id) && preg_match('/^(\w+)__(\w+)$/', $class, $m))
	{
		$class = $m[1];
		$object_id = $m[2];
		if(!is_numeric($m[2]))
		{
			if($object_id[0] == 'x')
				$object_id = substr($object_id, 1);
			$object_id = base64_decode($object_id);
		}
	}

//	echo "class_load($class, $object_id);<br/>\n";
	return class_load($class, $object_id, $args);
}

function &object_new($class, $id = NULL)
{
    $obj = new $class($id);

	if($id !== NULL)
	{
		$id = call_user_func(array($class, 'id_prepare'), $id);
		$obj->set_id($id);
	}

	$obj->_configure();

	$obj->init(false);

	return $obj;
}

function &object_new_instance($class, $id = NULL, $db_update = true, $need_check_data = false)
{
	if(is_array($id))
	{
		$data = $id;
		$id = @$data['id'];
	}
	else
		$data = false;

	if(!class_exists($class))
		throw new Exception("Class name '$class' not exists");

	$id = call_user_func(array($class, 'id_prepare'), $id, $class);
	$object = &object_new($class, $id);

	$object->set_owner_id(defval($data, 'owner_id', bors()->user_id()), true);
	$object->set_last_editor_id(bors()->user_id(), true);

	$replace = popval($data, '*replace', false);

	if(!$object->set_fields($data, true, NULL, $need_check_data) && $need_check_data)
	{
		bors()->drop_changed_object($object);
		$object = NULL;
		return $object;
	}

	$object->set_attr('__replace_on_new_instance', $replace);
	$object->new_instance();
	$object->_configure();
	return $object;
}

function bors_object_new_instance_db(&$object)
{
	$tab = $object->table_name();
	if(!$tab)
		debug_exit("Try to get new db instance with empty main table");

//	debug_trace();

	if(!$object->create_time(true))
		$object->set_create_time(time(), true);

	if(!$object->modify_time(true))
		$object->set_modify_time(time(), true);

//	$object->set_owner_id(bors()->user_id(), true);
	$object->set_last_editor_id(bors()->user_id(), true);

	$object->storage()->create($object);
}

function bors_db_fields_init($obj)
{
	foreach($obj->fields() as $db => $tables)
		foreach($tables as $tables => $fields)
			foreach($fields as $property => $db_field)
				$obj->data[is_numeric($property) ? $db_field : $property] = NULL;
}

$GLOBALS['bors_global'] = NULL;
function bors()
{
	if(is_null(@$GLOBALS['bors_global']))
		if(class_exists('bors_global'))
			$GLOBALS['bors_global'] = new bors_global(NULL);
		else
			$GLOBALS['bors_global'] = false;

	return $GLOBALS['bors_global'];
}

function bors_clear() { $GLOBALS['bors_global'] = NULL; }

function bors_exit($message = '')
{
	static $bors_exit_doing = false;
	if($bors_exit_doing)
		return true;

	$bors_exit_doing = true;

	echo $message;

	if(config('cache_static') && $message)
		cache_static::drop(bors()->main_object());

	bors()->changed_save();

	if(function_exists('error_get_last')) // Заразо. Оно только с PHP 5 >= 5.2.0
		$error = error_get_last();
	else
		$error = array('type' => 0);

    if ($error['type'] == 1)
    {
		if($out_dir = config('debug_hidden_log_dir'))
		{
			@mkdir(config('debug_hidden_log_dir').'/errors');
			if(file_exists(config('debug_hidden_log_dir').'/errors'))
			{
				$trace = debug_trace();
				debug_hidden_log('errors/'.date('c'), "Handled fatal error:
		errno={$error['type']}
		errstr={$error['message']}
		errfile={$error['file']}
		errline={$error['line']}", -1, array('append' => "errcontext=".print_r($trace, true)));
			}
		}
	}

	if(config('debug.show_variables'))
	{
		$deb = '';
		if($s = debug_vars_info())
			$deb = "\n=== debug vars info: ===\n$s";
		if($s = debug_count_info_all())
			$deb .= "\n=== debug counting: ===\n$s";
		if($s = debug_timing_info_all())
			$deb .= "\n=== debug timing: ===\n$s";
		echo $deb."\n";
	}

	if(!config('do_not_exit'))
		exit();

	return true;
}

function bors_parse_internal_uri($uri)
{
	echo "parse $uri<Br/>";
	if(!preg_match('!^(\w+)://(.+)$!', $uri, $m))
		return array(NULL, NULL);

	if(preg_match('!^(\w+)/$!', $m[2], $mm))
		$m[2] = $mm[1];

	return array($m[1], $m[2]);
}

function bors_drop_global_caches()
{
	unset($GLOBALS['bors_data']['global']['present']);
	unset($GLOBALS['HTS_GLOBAL_DATA']);
	unset($GLOBALS['bors_data']['cached_objects4']);
}

function bors_server_var($name, $default = NULL)
{
	$sv = objects_first('bors_var_db', array('name' => $name, 'order' => '-create_time'));
	return $sv ? $sv->value() : $default;
}

function bors_set_server_var($name, $value, $keep_alive = -1)
{
	$sv = objects_first('bors_var_db', array('name' => $name, 'order' => '-create_time'));

	if(!$sv)
	{
		$sv = object_new_instance('bors_var_db');
		$sv->set_name($name, true);
	}

	$sv->set_value($value, true);
	$sv->set_expire_time($keep_alive > 0 ? time() + $keep_alive : NULL, true);
	$sv->store();
}

function bors_stop_bots()
{
	if(bors()->client()->is_bot())
	{
		@header("HTTP/1.0 404 Not Found");
		return go('http://balancer.ru/forum/', true);
	}

	return false;
}

function bors_throw($message)
{
	throw new Exception($message);
}

/**
	Возвращает результат применения метода get() к объекту, если он существует.
	$def = NULL - в противном случае.
*/
function object_property($object, $property, $def = NULL)
{
	if(is_object($object))
	{
		if(preg_match('/^\w+$/', $property))
			return $object->get($property, $def);
		else
		{
//			echo debug_trace();
//			echo "\$x = \$object->{$property};";
			eval("\$x = \$object->{$property};");
			return $x;
		}
	}

	return $def;
}

function object_property_args($object, $property, $args = array(), $def = NULL)
{
	if(is_object($object))
		return call_user_func_array(array($object, $property), $args);

	return $def;
}

/**
	Возвращает истину, если классы объектов и их ID совпадают.
*/
function bors_eq($object1, $object2)
{
	return $object1->extends_class() == $object2->extends_class() && $object1->id() == $object2->id();
}

function bors_count($class_name, $where) { return objects_count($class_name, $where); }
function bors_load($class_name, $id) { return object_load($class_name, $id); }
function bors_load_ex($class_name, $id, $attrs) { return object_load($class_name, $id, $attrs); }
function bors_load_uri($uri) { return object_load($uri); }
function bors_find_all($class_name, $where) { return objects_array($class_name, $where); }
function bors_find_first($class_name, $where) { return objects_first($class_name, $where); }

function bors_each($class_name, $where)
{
	$class = new $class_name(NULL);
	$storage = $class->storage();
	return $storage->each($class_name, $where);
}

function bors_new($class_name, $data = array()) { return object_new_instance($class_name, $data); }
