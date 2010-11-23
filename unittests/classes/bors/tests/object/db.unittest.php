<?php

class bors_tests_object_db_unittest extends PHPUnit_Framework_TestCase
{
    public function testObject()
    {
		$object = object_load('bors_tests_object_db', -12345);
        $this->assertNull($object);
		$object = object_new_instance('bors_tests_object_db', array(
			'id' => '123',
			'title' => 'test',
		));
		$object->store();
        $this->assertNotNull($object);

		$first = objects_first('bors_tests_object_db', array('title' => 'test'));
        $this->assertNotNull($first);
        $this->assertEquals('123', $first->id());
		$time = '123456789';
        $first->set_create_time($time, true);
        $first->store();

		$object = objects_first('bors_tests_object_db', array('create_time' => $time));
        $this->assertNotNull($object);
        $this->assertEquals('123', $object->id());
    }

	public function setUp()
	{
		$se = call_user_func(array('bors_tests_object_db', 'storage_engine'));
		$se = object_load($se);
		$se->drop_table('bors_tests_object_db');
		$se->create_table('bors_tests_object_db');
	}
}
