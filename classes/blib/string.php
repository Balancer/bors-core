<?php

class blib_string extends blib_object
{
	function __construct($init_value = NULL)
	{
		$this->_value = (string) $init_value;
	}

	function __toString() { return $this->_value; }

	function write() { echo $this->_value; return $this; }
	function writeln() { echo $this->_value, PHP_EOL; return $this; }
	function debug_write() { var_dump($this->_value); return $this; }
	function len() { return bors_strlen($this->_value); }
	function length() { return bors_strlen($this->_value); }
	function upper() { $this->_value = bors_upper($this->_value); return $this; }
	function lower() { $this->_value = bors_lower($this->_value); return $this; }

	function __unit_test($test)
	{
		$s = "Hello, world!";
		$bs = new blib_string($s);
		$test->assertEquals($s, (string) $bs);
		$test->assertEquals(bors_upper($s), $bs->upper()->value());
		$test->assertEquals(bors_lower($s), $bs->lower()->val());
		$test->assertEquals(13, $bs->len());
	}
}
