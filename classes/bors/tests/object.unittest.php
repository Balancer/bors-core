<?php

class bors_tests_object_unittest extends PHPUnit_Framework_TestCase
{
    public function testObject()
    {
		$object = object_load('bors_tests_object');
        $this->assertNotNull($object);
        $this->assertEquals($object->test_method(), 'test_method');

		// Проверка работы свойств, вызываемых как методы
        $this->assertEquals($object->test_property(), 'test property');

		$object->set_qwertyasdf('test', false);
        $this->assertEquals($object->qwertyasdf(), 'test');

		// Имя объекта по умолчанию - имя его класса
        $this->assertEquals($object->title(), 'bors_tests_object');
    }
}
