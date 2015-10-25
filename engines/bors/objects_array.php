<?php

require_once(BORS_CORE.'/inc/mysql.php');

/**
 * @param string $class_name
 * @param array $args
 * @return mixed array
 */
function bors_find_all($class_name, $args = array())
{
	if(is_numeric($class_name))
		$class_name = class_id_to_name($class_name);

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

	if(!empty($args['*preload']))
	{
		$preload = explode(',', $args['*preload']);
		unset($args['*preload']);
	}

	if(!preg_match('/^[\\\\\w]+$/', $class_name))
	{
		bors_debug::syslog('data-errors', "Incorrect class name {$class_name} objects_load by ".print_r($args, true));
		return array();
	}

	if(!class_exists($class_name))
	{
		bors_debug::syslog('class-name-error', "Not found classname '$class_name'. May be admin-class in link?");
		return array();
	}

    /** @var bors_object $init */
	$init = new $class_name(NULL);
	$class_file = bors_class_loader::load_file($class_name);
	$init->set_class_file($class_file);

	if($s = $init->storage())
	{
		if(method_exists($s, 'load_array'))
			$objects = $s->load_array($init, array_merge($cargs, $args));
		else
			$objects = $s->load($init, mysql_args_compile($args, $class_name), false, $cargs);

		if(config('debug_objects_create_counting_details'))
		{
			debug_count_inc("bors_find_all($class_name) calls");
			debug_count_inc("bors_find_all($class_name) count", count($objects));
		}

		if(config('debug.trace_object_load'))
			bors_debug::syslog('objects_load', "all $class_name(".str_replace("\n", " ", print_r($args, true)).")", config('debug_trace_object_load_trace'));

		if(!empty($preload))
		{
			foreach($preload as $x)
				if(preg_match('/^(\w+)\((\w+)\)$/', $x, $m))
					bors_objects_preload($objects, $m[2], $m[1]);
		}

		return $objects;
	}

	bors_debug::syslog('__fatal_objects_error', 'Try to load objects array without storage: '.$init);
	return array();
}

/**
 * Ищет первый подходящий по запросу объект.
 * @param string $class_name
 * @param array $args
 * @return bors_object|null
 */
function bors_find_first($class_name, $args = array())
{
	if(empty($args['limit']))
		$args['limit'] = 1;
	
    $objs = bors_find_all($class_name, $args);
	
    if(config('debug_objects_create_counting_details'))
		debug_count_inc("bors_find_first($class_name)");

	if(config('debug.trace_object_load'))
		bors_debug::syslog('objects_load', "first $class_name(".str_replace("\n", " ", print_r($args, true)).")", config('debug_trace_object_load_trace'));

	return $objs ? $objs[0] : NULL;
}

/**
 * @param string|object $class_name
 * @param array $args
 * @return integer
 * @throws Exception
 */
function bors_count($class_name, $args = array())
{
	if(is_numeric($class_name))
		$class_name = class_id_to_name($class_name);

    //TODO: посмотреть, если нигде не используется вариант подсчёта для объектов, то выкинуть.
    $init = is_object($class_name) ? $class_name : new $class_name(NULL);

	$storage = $init->storage();
	if(method_exists($storage, 'count'))
		return $storage->count($init, $args);

	$where = mysql_args_compile($args, $class_name);

	$cargs = array();

	if(!empty($args['object_id']))
		$cargs['object_id'] = $args['object_id'];

	if(!$storage)
		bors_throw("Empty storage for ".$class_name);

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
