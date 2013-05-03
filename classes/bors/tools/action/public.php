<?php

class bors_tools_action_public extends bors_page
{
	function pre_show()
	{
		$params = explode('/', $this->id());

		$class = array_shift($params);
		if(count($params) == 0)
		{
			// Если без метода, то просто выводим страницу (AJAX?)
			$method = NULL;
			$id = NULL;
		}
		elseif(count($params) == 1)
		{
			$method = array_shift($params);
			$id = NULL;
		}
		else
		{
			$id 	= array_shift($params);
			$method = array_shift($params);
		}

		$params = bors_lib_urls::parse_query_string($params);
		$params = array_merge($params, $_GET);

		if($method)
			$method = "public_action_$method";

		if($id)
			$object = bors_load($class, $id);
		else
			$object = bors_foo($class);

		if(!$object)
			bors_throw("Can't load class '$class'");

		$object->set_args($params);

		if(!$method)
			return $object->content();

		if(!method_exists($object, $method))
			bors_throw("Can't find method '$method' in class '$class'");

		return $object->$method($params);
	}
}
