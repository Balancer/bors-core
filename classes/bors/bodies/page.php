<?php

require_once('engines/lcml/main.php');

class bors_bodies_page extends base_null
{
	function body($object)
	{
		$data = array();

		$data['template_dir'] = $object->class_dir();
		$data['this'] = $object;
		$data['self'] = $object;

		$body_template = $object->body_template();

		$object->template_data_fill();

		foreach(explode(' ', $object->template_local_vars()) as $var)
			$data[$var] = $object->$var();

		$data = array_merge($data,
			defval(@$GLOBALS['cms']['templates'], 'data', array()),
			$object->local_template_data_array()
		);

		if(config('debug.execute_trace'))
			debug_execute_trace("{$object->body_template_class()}::fetch()");

		return call_user_func(
			array($object->body_template_class(), 'fetch'),
			$body_template,
			$data);
	}
}
