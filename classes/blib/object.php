<?php

class blib_object
{
	protected $_value = NULL;

	function val() { return $this->_value; }
	function value() { return $this->_value; }

	function __construct($init_value = NULL)
	{
		$this->_value = $init_value;
	}
}
