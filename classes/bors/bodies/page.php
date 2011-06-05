<?php

require_once('engines/lcml/main.php');

class bors_bodies_page extends base_null
{
	function body($object)
	{
		$data = array();

		$data['template_dir'] = $object->class_dir();
		$data['this'] = $object;

		$object->template_data_fill();

		$body_template = $object->body_template();

		return call_user_func(
			array($object->body_template_class(), 'fetch'),
			$body_template,
			$data);
	}
}
