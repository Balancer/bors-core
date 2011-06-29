<?php

class bors_unittest_auto_objects_helper extends bors_object
{
	var $count = 0;
	function count() { return $this->count++; }
}

class bors_unittest_auto_objects extends bors_object
{
	function auto_objects()
	{
		return array(
			'object' => 'bors_unittest_auto_objects_helper(id)',
		);
	}
}

class auto_objects_unittest extends PHPUnit_Framework_TestCase
{
    public function test_caching()
    {
		$test = bors_load('bors_unittest_auto_objects', 123);
		$this->assertEquals(0, $test->object()->count());
		$this->assertEquals(1, $test->object()->count());
		$this->assertEquals(2, $test->object()->count());
		$test->set_id(456);
		$this->assertEquals(0, $test->object()->count());
		$this->assertEquals(1, $test->object()->count());
	}
}
