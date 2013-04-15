<?php

class blib_null extends blib_object
{
	function is_empty() { return true; }
	function is_null()  { return true; }
	function is_value() { return false; }
	function is_array() { return false; }

	function __call($name, $args) { return $this; }
	function __toString() { return  ''; }
}
