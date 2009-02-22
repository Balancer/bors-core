<?php

function bors_tpl_module($class_name, $args = array())
{
	extract($args);

	if(!$id)
		$id = bors()->main_object();
			
	$obj = object_load('module_'.$class_name, $id);
		
	if(!$obj)
		return "Can't load module 'module_{$class_name}'";

	return $obj->body();
}
