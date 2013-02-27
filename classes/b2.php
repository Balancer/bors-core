<?php

class b2
{
	function load($class_name, $object_id)
	{
		return bors_load($class_name, $object_id);
	}

	function find($class_name)
	{
		return new b2_core_find($class_name);
	}
}
