<?php

require_once('bors/vhosts_loader.php');
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
	{
		static $load_counter = 0;
		echo "Load {$class}({$object_id}, ".serialize($args).")<br />\n";
		if($load_counter++ > config('debug_object_load_limit'))
			debug_exit('Object load limit exceed.');
	}

	if(!$class)
		return;

	return class_load($class, $object_id, $args);
}

function &object_new($class, $id = NULL)
{
    $obj = &new $class($id);

	if($id !== NULL)
	{
		$id = call_user_func(array($class_name, 'id_prepare'), $id);
		$obj->set_id($id);
	}
	
	$obj->_configure();

	$obj->init(false);

	return $obj;
}

function &object_new_instance($class, $id = NULL, $db_update = true)
{
	if(is_array($id))
	{
		$data = $id;
		$id = NULL;
	}
	else
		$data = false;

	$id = call_user_func(array($class_name, 'id_prepare'), $id);
	$obj = &object_new($class, $id);

	if($data !== false)
		foreach($data as $key => $value)
			$obj->set($key, $value, $db_update);

	$obj->new_instance();
	$obj->_configure();
	return $obj;
}

function bors_object_new_instance_db(&$object)
{
	$tab = $object->main_table_storage();
	if(!$tab)
		debug_exit("Try to get new db instance with empty main table");

//	debug_trace();

	if(!$object->create_time(true))
		$object->set_create_time(time(), true);

	if(!$object->modify_time(true))
		$object->set_modify_time(time(), true);

	$object->storage()->create($object);
}

function bors_db_fields_init($obj)
{
	foreach($obj->fields() as $db => $tables)
	{
		foreach($tables as $tables => $fields)
		{
			foreach($fields as $property => $db_field)
			{
				if(is_numeric($property))
					$property = $db_field;

				$obj->{'stb_'.$property} = NULL;
			}
		}
	}
}

function defval($data, $name, $default=NULL)
{
	if(!isset($data[$name]))
		return $default;
	
	return $data[$name];
}


$GLOBALS['bors_global'] = NULL;
function bors()
{
	if($GLOBALS['bors_global'] == NULL)
		$GLOBALS['bors_global'] = &new bors_global(NULL);

	return $GLOBALS['bors_global'];
}

function bors_exit($message = 0)
{
	global $bors_exit_doing;
	if(!empty($bors_exit_doing))
		return;
	
	$bors_exit_doing = true;
	cache_static::drop(bors()->main_object());
	bors()->changed_save();
	$bors_exit_doing = false;
	exit($message);
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
