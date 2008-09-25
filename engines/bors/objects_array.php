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

	$where = mysql_args_compile($args);
		
	$init = &new $class(NULL);

	return $init->storage()->load($init, $where, false, $cargs);
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

	$where = mysql_args_compile($args);

	$cargs = array();

	if(!empty($args['object_id']))
		$cargs['object_id'] = $args['object_id'];

	return $init->storage()->load($init, $where, true, $cargs);
}
