<?php

require_once('bors/names.php');
require_once('bors/messages.php');
require_once('bors/objects_array.php');
require_once('bors/object_loader.php');

function object_load($class, $object_id=NULL, $args=array())
{
	if(is_numeric($class))
		$class = class_id_to_name($class);
	
//	echo "Load {$class}({$object_id}, ".serialize($args).")<br />\n";
	
	if(!$class)
		return;
	
	return class_load($class, $object_id, $args);
}

function object_new($class) { return object_load($class); }

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
