<?php

/**
	Базовый класс для всяких нестандартных типов полей объектов.
	Например, bors_time.
*/

class bors_property extends bors_object_simple
{
	protected $_value		= NULL;
	protected $_is_set		= false;
	protected $_need_store	= false;

	function val() { return $this->_value; }
	function set($value, $need_store = true)
	{
		$this->_value		= $value;
		$this->_is_set		= true;
		$this->_need_store	= $need_store;
		return $this;
	}

	static function __unit_test($suite)
	{
		$property = new bors_property();
		$suite->assertNull($property->val());

		$suite->assertEquals('abc', $property->set('abc')->val());
		$suite->assertEquals('abc', $property->val());
	}
}
