<?php

function bors_class_names_load($reload = false)
{
	global $loaded;

	if(!empty($loaded) && !$reload)
		return $loaded;

	$loaded = array();
	$loaded[0] = array();
	$loaded[1] = array();
	$db = new driver_mysql(config('main_bors_db'));
	foreach($db->get_array('SELECT * FROM bors_class_names') as $x)
	{
		$loaded[0][$x['id']] = $x['name'];
		$loaded[1][$x['name']] = $x['id'];
	}

	return $loaded;
}

function class_name_to_id($object)
{
	if(is_object($object))
		$class_name = $object->extends_class();
	else
		$class_name = $object;

	if(strlen($class_name) > 64)
	{
		debug_hidden_log('class-loader-errors', "Too long class name: '$class_name'");
		bors_exit("Too long class name: '$class_name'");
	}

	if(!$class_name)
		return NULL;

	if(in_array($class_name, config('classes_skip', array())))
		return NULL;

	if(!config('main_bors_db'))
		return $class_name;

	$loaded = bors_class_names_load();
	if($class_id = @$loaded[1][$class_name])
		return $class_id;

	$db = new driver_mysql(config('main_bors_db'));
	$db->insert('bors_class_names', array('name' => $class_name));
	$class_id = $db->last_id();
	$db->close();

	bors_class_names_load(true);

	return $class_id;
}

function class_names_to_ids($class_names)
{
	$class_ids  = array();

	foreach($class_names as $class_name)
		$class_ids[] = class_name_to_id($class_name);

	return $class_ids;
}

function class_id_to_name($class_id)
{
	$loaded = bors_class_names_load();
	if($class_name = @$loaded[0][$class_id])
		return $class_name;

	debug_hidden_log('class-loader-errors', "Unknown class ID: '$class_id'");
	return NULL;
}
