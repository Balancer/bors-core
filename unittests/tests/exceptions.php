<?php

class exceptions_unittest_helper extends bors_object
{
	function show()
	{
		bors_throw('Test exception');
	}
}

class exceptions_unittest extends PHPUnit_Framework_TestCase
{
    public function test_exceptions()
    {
		$x = bors_load('exceptions_unittest_helper', NULL);
		$this->assertNotNull($x);
		try { $s = $x->show(); }
		catch(Exception $e) { $this->assertEquals('Test exception', $e->getMessage()); }
	}
}
