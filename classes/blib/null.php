<?php

class blib_null extends blib_object
{
	function is_empty() { return true; }
	function exists() { return false; }
	function is_null()  { return true; }
	function is_not_null()  { return false; }
	function is_value() { return false; }
	function is_array() { return false; }

	function __call($name, $args) { return $this; }
	function __toString() { return  ''; }

	// This NULL-object. Always return default.
	function get($property_foo, $default = NULL)
	{
		// If need return NULL, then return blib_null
		if(is_null($default))
			$default = $this;

		return $default;
	}
}
