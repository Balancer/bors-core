<?php

function bors_tpl_module($class_name, $args = array()) { return bors_tpl_module_ex($class_name, $args); }

function bors_tpl_module_ex($class_name, $args)
{
//	var_dump($args);
	$args['no_load_cache'] = true;
	$obj = object_load($class_name, NULL, $args);

	if(!$obj)
		$obj = object_load('module_'.$class_name, NULL, $args);

	if(!$obj)
		return "Can't load module 'module_{$class_name}'";

	return $obj->body();
}
