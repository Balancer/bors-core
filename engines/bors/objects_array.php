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
		$cargs['by_id'] = $args['by_id'];
	unset($args['by_id']);

	if(!empty($args['select']))
		$cargs['select'] = $args['select'];
	unset($args['select']);

	if(($preload = @$args['*preload']))
	{
		unset($args['*preload']);
		$preload = explode(',', $preload);
	}

	if(!preg_match('/^\w+$/', $class))
	{
		debug_hidden_log('data-errors', "Incorrect class name {$class} objects_load by ".print_r($args, true));
		return array();
	}

	if(!class_exists($class))
	{
		debug_hidden_log('class-name-error', "Not found classname '$class'. May be admin-class in link?");
		return array();
	}

	$init = new $class(NULL);
	$class_file = bors_class_loader::load($class);
	$init->set_class_file($class_file);

	if($s = $init->storage())
	{
		if(method_exists($s, 'load_array'))
			$objects = $s->load_array($init, array_merge($cargs, $args));
		else
			$objects = $s->load($init, mysql_args_compile($args, $class), false, $cargs);

		if(config('debug_objects_create_counting_details'))
		{
			debug_count_inc("bors_find_all($class) calls");
			debug_count_inc("bors_find_all($class) count", count($objects));
		}

		if(config('debug_trace_object_load'))
			debug_hidden_log('objects_load', "all $class(".str_replace("\n", " ", print_r($args, true)).")", config('debug_trace_object_load_trace'));

		if($preload)
		{
			foreach($preload as $x)
				if(preg_match('/^(\w+)\((\w+)\)$/', $x, $m))
					bors_objects_preload($objects, $m[2], $m[1]);
		}

		return $objects;
	}

	debug_hidden_log('__fatal_objects_error', 'Try to load objects array without storage: '.$init);
	return array();
}

function objects_first($class, $args = array())
{
	if(empty($args['limit']))
		$args['limit'] = 1;
	$objs = objects_array($class, $args);
	if(config('debug_objects_create_counting_details'))
		debug_count_inc("bors_find_first($class)");

	if(config('debug_trace_object_load'))
		debug_hidden_log('objects_load', "first $class(".str_replace("\n", " ", print_r($args, true)).")", config('debug_trace_object_load_trace'));

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

	$storage = $init->storage();
	if(method_exists($storage, 'count'))
		return $storage->count($init, $args);

	$where = mysql_args_compile($args, $class);

	$cargs = array();

	if(!empty($args['object_id']))
		$cargs['object_id'] = $args['object_id'];

	return $storage->load($init, $where, true, $cargs);
}

function bors_field_array_extract($objects_array, $field)
{
	$result = array();
	foreach($objects_array as $x)
		$result[] = is_object($x) ? $x->get($field) : NULL;

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

	$init = new $class(NULL);

	foreach($init->storage()->load($init, $where, false, $cargs) as $x)
		$x->delete();
}

function bors_titled_links($objects, $admin = false)
{
	if(empty($objects))
		return '';

	$result = '';
	foreach($objects as $x)
		if($admin)
			$result[] = $x->admin()->imaged_titled_link();
		else
			$result[] = $x->titled_link();

	return join(', ', $result);
}
