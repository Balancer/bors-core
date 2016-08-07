<?php

namespace B2;

class Obj extends \bors_object
{
	static function factory($id=NULL)
	{
		$called_class_name = get_called_class();
		$object = new $called_class_name($id);
		$object->b2_configure();
		return $object;
	}
}
