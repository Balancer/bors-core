<?php

class bors_tools_action_public extends bors_page
{
	function pre_show()
	{
		$params = explode('/', $this->id());
		$class = array_shift($params);
		$method = array_shift($params);

		$params = bors_lib_urls::parse_query_string($params);
		$params = array_merge($params, $_GET);

		$object = bors_load($class, NULL);
		if(!$object)
			bors_throw("Can't load class '$class'");

		$method = "public_action_$method";
		if(!method_exists($object, $method))
			bors_throw("Can't find method '$method' in class '$class'");

		$object->set_args($params);
		return $object->$method();
	}
}
