<?php

class bors_tests_storage_mysql_db_unittest extends PHPUnit_Framework_TestCase
{
    public function testObject()
    {
		$object = bors_load('bors_tests_storage_mysql_db', -12345);
        $this->assertNull($object);
		$object = object_new_instance('bors_tests_storage_mysql_db', array(
			'id' => '123',
			'title' => 'test',
		));
		$object->store();
        $this->assertNotNull($object);

		$first = bors_find_first('bors_tests_storage_mysql_db', array('title' => 'test'));
        $this->assertNotNull($first);
        $this->assertEquals('123', $first->id());
		$time = '123456789';
        $first->set_create_time($time, true);
        $first->store();

		$object = bors_find_first('bors_tests_storage_mysql_db', array('create_time' => $time));
        $this->assertNotNull($object);
        $this->assertEquals('123', $object->id());


		// Создаём новый объект с автоопределяемым ID. Предыдущий был 123, этот должен стать 124.
		$object = object_new_instance('bors_tests_storage_mysql_db', array(
			'title' => 'test2',
			'create_time' => -1,
		));
		$object->store();
        $this->assertNotNull($object);
        $this->assertEquals('124', $object->id());

		// Мы прописали отрицательное время создания. Попробуем найти эту запись.
		$x = bors_find_first('bors_tests_storage_mysql_db', array('create_time<' => 0));
        $this->assertEquals('test2', $x->title());
    }

	public function setUp()
	{
		$se = call_user_func(array('bors_tests_storage_mysql_db', 'storage_engine'));
		$se = new $se;
		$se->drop_table('bors_tests_storage_mysql_db');
		$se->create_table('bors_tests_storage_mysql_db');
	}
}
