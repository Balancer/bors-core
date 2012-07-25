<?php

class bors_config extends base_config
{
	function config_class() { return NULL; } // Workaround ошибки, когда для конфиг-класса пытается грузиться конфиг-класс.

	function object_data() { return array(); }

	function __construct(&$object)
	{
		$this->set_id($object);

		foreach($this->object_data() as $key => $value)
			$object->set_attr($key, $value);

		parent::__construct($object);
	}
}
