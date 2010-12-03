<?php

class bors_class_namesFullList extends base_list
{
	function named_list()
	{
		$names = bors_find_all('bors_class_name', array());
		$result = array();
		foreach($names as $x)
		{
			if(!$x)
				continue;

			$class_name = $x->name();
			if(!preg_match('/^\w+$/', $class_name) || !class_include($class_name))
				continue;

			$class = new $class_name;

			if($class_title = $class->class_title())
				$result[$class_name] = $class_title;
		}

//		asort($result);

		return $result;
	}
}
