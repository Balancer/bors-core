<?php

class blib_value extends blib_object
{
	function __construct($init_value = NULL)
	{
		if(is_array($init_value))
			return $this->_value = $init_value;

		$this->_value = array();
	}

	function map($function)
	{
		$this->_value = array_map($function, $this->_value);
		return $this;
	}

	function filter()
	{
		$this->_value = array_filter($this->_value);
		return $this;
	}

	function unique()
	{
		$this->_value = array_unique($this->_value);
		return $this;
	}
}
