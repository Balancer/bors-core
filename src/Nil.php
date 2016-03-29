<?php

namespace B2;

class Nil extends Obj
{
	function __call($method, $params)
	{
		return $this;
	}

	function __get($property)
	{
		return $this;
	}

	function __toString() { return ''; }

	function isNull() { return true; }

	static function __unit_test($suite)
	{
		$null = Nil::factory();
		$suite->assertNotNull($null->foo());
		$suite->assertNotNull($null->foo);
		$suite->assertTrue($null->isNull());
	}
}
