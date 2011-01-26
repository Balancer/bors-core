<?php

// В этом классе не должно быть ни одного свойства! Только методы!

class base_null
{
	function can_be_empty() { return true; }
	function class_name() { return get_class($this); }
	function init() { return false; }
	function _configure() { return false; }
	function can_cached() { return false; }
	function loaded() { return false; }
	function set_class_file($foo) { }
	static function id_prepare($id) { return $id; }
	function __toString() { return $this->class_name().'://!'; }
	function id() { return NULL; }
}
