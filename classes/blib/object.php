<?php

class blib_object
{
	private $_value = NULL;
	function value() { return $this->_value; }

	function __construct($init_value = NULL)
	{
		$this->_value = $init_value;
	}
}
