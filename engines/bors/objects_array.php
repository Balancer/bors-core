<?php

require_once('inc/mysql.php');

function objects_array($class, $args = array())
{
	if(is_numeric($class))
		$class = class_id_to_name($class);

	$cargs = array();

	if(!empty($args['object_id']))
		$cargs['object_id'] = $args['object_id'];
	unset($args['object_id']);

	if(!empty($args['by_id']))
		$cargs['by_id'] = true;
	unset($args['by_id']);

	$where = mysql_args_compile($args, $class);

	$init = &new $class(NULL);

	if($s = $init->storage())
		return $s->load($init, $where, false, $cargs);

	debug_hidden_log('__fatal_objects_error', 'Try to load objects array without storage');
	return array();
}

function objects_first($class, $args = array())
{
	if(empty($args['limit']))
		$args['limit'] = 1;
	$objs = objects_array($class, $args);
	return $objs ? $objs[0] : NULL;
}

function objects_count($class, $args = array())
{
	if(is_numeric($class))
		$class = class_id_to_name($class);

	if(is_object($class))
		$init = $class;
	else	
		$init = new $class(NULL);

	$where = mysql_args_compile($args, $class);

	$cargs = array();

	if(!empty($args['object_id']))
		$cargs['object_id'] = $args['object_id'];

	return $init->storage()->load($init, $where, true, $cargs);
}

function bors_field_array_extract($objects_array, $field)
{
	$result = array();
	foreach($objects_array as $x)
		$result[] = $x->$field();

	return $result;
}

function bors_fields_array_extract($objects_array, $fields_array)
{
	$result = array();
	foreach($objects_array as $x)
		foreach($fields_array as $field)
			$result[$field][] = $x->$field();

	return $result;
}

function objects_delete($class, $args = array())
{
	if(is_numeric($class))
		$class = class_id_to_name($class);

	$cargs = array();

	if(!empty($args['object_id']))
		$cargs['object_id'] = $args['object_id'];
	unset($args['object_id']);

	if(!empty($args['by_id']))
		$cargs['by_id'] = true;
	unset($args['by_id']);

	$where = mysql_args_compile($args, $class);

	$init = &new $class(NULL);

	foreach($init->storage()->load($init, $where, false, $cargs) as $x)
		$x->delete();
}
