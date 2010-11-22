<?php

class bors_config extends base_config
{
	var $object_data = array();

	function __construct(&$object)
	{
		foreach($this->object_data() as $key => $value)
			$object->set($key, $value, false);

		parent::__construct($object);
	}
}
