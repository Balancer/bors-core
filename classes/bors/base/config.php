<?php

class base_config extends base_empty
{
	function __construct(&$object)
	{
		parent::__construct($object);
	
		foreach($this->config_data() as $key => $value)
			$object->set($key, $value, false);

		foreach($this->template_data() as $key => $value)
			$object->add_template_data($key, $value);
	}
	
	function template_init() { }
	function config_data() { return array(); }
	function template_data() { return array(); }
}
