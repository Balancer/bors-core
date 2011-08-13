<?php

class modules_unittest_helper extends bors_module
{
	function body()
	{
		if($ret = $this->arg('foo', 'bar'))
			return $ret;

		if($ret = $this->args('arg', 'bar2'))
			return $ret;

		if($this->arg('exception'))
			return new driver_mysql('NON EXISTS DATABASE');

		return 'ok';
	}
}

class modules_unittest extends PHPUnit_Framework_TestCase
{
    public function test_modules()
    {
		bors_debug::log('test', 'hello world!', 'debug');

		$x = bors_load_ex('modules_unittest_helper', NULL, array(
		));
		$this->assertNotNull($x);
		$this->assertEquals('bar', $x->html_code());

		$x = bors_load_ex('modules_unittest_helper', NULL, array(
			'foo' => false,
		));

		$this->assertNotNull($x);
		$this->assertEquals('bar2', $x->html_code());

		$x = bors_load_ex('modules_unittest_helper', NULL, array(
			'foo' => false,
			'arg' => false,
		));

		$this->assertNotNull($x);
		$this->assertEquals('ok', $x->html_code());

		$x = bors_load_ex('modules_unittest_helper', NULL, array(
			'foo' => false,
			'arg' => false,
			'exception' => true,
		));

		$this->assertNotNull($x);
		//TODO: разобраться с регекспами
		//$this->assertRegExp("!mysql_connect(, ) to DB 'NON EXISTS DATABASE!", $x->html_code());
	}
}
