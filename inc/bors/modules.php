<?php

function bors_tpl_module($class_name, $args = array())
{
	$args['no_load_cache'] = true;
	$obj = object_load('module_'.$class_name, NULL, $args);
		
	if(!$obj)
		return "Can't load module 'module_{$class_name}'";

	return $obj->body();
}
