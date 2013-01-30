<?php

/**
	Самый примитивный загружаемый класс, понимаемый основными классами фреймворка.

	В этом классе не должно быть ни одного свойства! Только методы!
*/


class bors_object_empty
{
	function can_be_empty() { return true; }
	function class_name() { return get_class($this); }
	function data_load() { return false; }
	function _configure() { return false; }
	function can_cached() { return false; }
	function is_loaded() { return false; }
	function set_class_file($foo) { }
	static function id_prepare($id) { return $id; }
	function __toString() { return $this->class_name().'://!'; }
	function id() { return NULL; }

	static function __unit_test($suite)
	{
		$object = bors_load('bors_object_empty', NULL);
		$suite->assertNotNull($object);
		$suite->assertNull($object->id());
		$suite->assertEquals('bors_object_empty', $object->class_name());
	}
}
