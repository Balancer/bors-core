<?php

class blib_array extends blib_object
{
	function __construct($init_value = NULL)
	{
		if(is_array($init_value))
			return $this->_value = $init_value;

		$this->_value = array();
	}

	static function factory($array) { return new blib_array($array); }

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

	function each($function)
	{
		foreach($this->_value as &$x)
			$function($x);

		return $this;
	}

	function __toString()
	{
		return blib_string::factory(print_r($this->_value, true));
	}

	function join($delimiter)
	{
		return blib_string::factory(join($delimiter, $this->_value));
	}

	function json()
	{
		return json_encode($this->_value);
	}

	function __unit_test($suite)
	{
		$x = blib_array::factory(array(1, 2, 3));
		$x->map(create_function('$x', 'return $x*$x;'));
		$x->each(create_function('&$x', '$x = $x*$x;'));
		$suite->assertEquals('1 16 81', $x->join(' '));
		$suite->assertEquals('[1,16,81]', $x->json());
	}
}
