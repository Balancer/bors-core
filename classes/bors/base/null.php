<?php

class base_null
{
	function can_be_empty() { return true; }
	function class_name() { return get_class($this); }
	function get_class_static($skip = 0)
	{
		$bt = debug_backtrace();
		// note that we are using $bt[1] instead of $bt[0];
		//$bt[0] would return the get_class_static function rather than the calling class
		$name = $bt[$skip+1]['class'];
		return $name;
	}
	function init() { return false; }
	function _configure() { return false; }
	function can_cached() { return false; }
	function loaded() { return false; }
	function set_class_file() { }
	static function id_prepare($id) { return $id; }
}
