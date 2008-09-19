<?php

class base_null
{
	function can_be_empty() { return true; }
	function class_name() { return get_class($this); }
	function init() { return false; }
	function can_cached() { return false; }
	function loaded() { return false; }
	function set_class_file() { }
}
