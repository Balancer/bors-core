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
	
//	echo "Load {$class}({$object_id} (".serialize($args).")<br />\n";
	
	if(!$class)
		return;
	
	return class_load($class, $object_id, $args);
}

function object_new($class) { return object_load($class); }

function object_new_instance($class, $id = NULL)
{
	$obj = object_load($class, $id, array('no_load_cache' => true));
	
	if(!$obj)
	{
//	    debug_exit("Can't make new instance for $class");
	    $obj = new $class($id);
		$obj->new_instance($id);
	}
	
	if(!$obj->id())
		$obj->new_instance($id);

	if($id !== NULL)
		$obj->set_id($id);
		
	return $obj;
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
	bors()->changed_save();
	exit($message);
	return true;
}
