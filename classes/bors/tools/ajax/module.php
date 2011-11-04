<?php

class bors_tools_ajax_module extends bors_object
{
	function content()
	{
		$params = explode('/', $this->id());
		$class = array_shift($params);
//		header("Content-type: text/plain"); // для тестов

		$params = bors_lib_urls::parse_query_string($params);
		$params = array_merge($params, $_GET);

		$object = bors_load($class, NULL);
		if(!$object)
			bors_throw("Can't load module class '$class'");

		$object->set_args($params);
		return $object->html();
	}
}
