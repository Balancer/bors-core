<?php

require_once __DIR__.'/../../../engines/lcml/main.php';

class bors_bodies_page extends base_null
{
	function body($object)
	{
		$data = array();

		$data['template_dir'] = $object->class_dir();
		$data['this'] = $object;
		$data['self'] = $object;

		$body_template = $object->body_template();

		$object->body_data_fill();

		// 'create_time description id modify_time nav_name title'
		foreach(explode(' ', $object->template_local_vars()) as $var)
			$data[$var] = $object->$var();

		$data = array_merge($data,
			defval(@$GLOBALS['cms']['templates'], 'data', array()),
			$object->local_template_data_array()
		);

		if(config('debug.execute_trace'))
			debug_execute_trace("{$object->body_template_class()}::fetch()");

		$html = call_user_func(
			array($object->body_template_class(), 'fetch'),
			$body_template,
			$data
		);

		return bors_lcml::output_parse($html);
	}
}
