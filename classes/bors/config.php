<?php

class bors_config extends base_config
{
	function config_class() { return NULL; } // Workaround ошибки, когда для конфиг-класса пытается грузиться конфиг-класс.

	function object_data() { return array(); }

	function __construct(&$object)
	{
		foreach($this->object_data() as $key => $value)
			$object->set($key, $value, false);

		parent::__construct($object);
	}
}
