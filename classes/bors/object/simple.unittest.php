<?php

class bors_object_simple_unittest extends PHPUnit_Framework_TestCase
{
	public function test()
	{
		$x = bors_load('bors_object_simple', 12345);
		$this->assertNotNull($x);

		// Проверка ID
		$this->assertEquals(12345, $x->id());

		// Проверка установки атрибутов
		$x->set_attr('test_attr', 'qwerty');
		$this->assertEquals('qwerty', $x->attr('test_attr'));

		$x->set_attr('test_attr2', 'asdfgh');
		$this->assertEquals('asdfgh', $x->attr('test_attr2'));
		$this->assertEquals('qwerty', $x->attr('test_attr'));

		// Значения по умолчанию
		$this->assertNull($x->attr('test_attr3'));
		$this->assertEquals('zxcvb', $x->attr('test_attr3', 'zxcvb'));

		// В явном виде кеширование значений не используем, повторно - снова NULL
		$this->assertNull($x->attr('test_attr3'));
	}
}
