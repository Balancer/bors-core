<?php

namespace B2;

class Object extends \bors_object
{
	static function factory($id=NULL)
	{
		$called_class_name = get_called_class();
		$object = new $called_class_name($id);
		$object->_configure();
		return $object;
	}
}
