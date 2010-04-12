<?php

class bors_tests_object_unittest extends PHPUnit_Framework_TestCase
{
    public function testObject()
    {
		$object = object_load('bors_tests_object');
        $this->assertNotNull($object);
        $this->assertEquals($object->test_method(), 'test_method');

		$object->set_qwertyasdf('test', false);
        $this->assertEquals($object->qwertyasdf(), 'test');
    }

    public function testObjectLoad()
    {
		$object = object_load('bors_tests_object');
        $this->assertNotNull($object);
        $this->assertEquals($object->test_method(), 'test_method');

		$object->set_qwertyasdf('test', false);
        $this->assertEquals($object->qwertyasdf(), 'test');
    }
}
