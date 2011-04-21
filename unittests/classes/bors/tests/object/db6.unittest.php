<?php

class bors_tests_object_db6_unittest extends PHPUnit_Framework_TestCase
{
	public function testObject()
	{
		$class = 'bors_tests_object_db6';
		$object = object_load($class, -12345);
        $this->assertNull($object);
		$object = object_new_instance($class, array(
			'id' => '123',
			'title' => 'test',
		));
		$object->store();
        $this->assertNotNull($object);

		$first = objects_first($class, array('title' => 'test'));
        $this->assertNotNull($first);
        $this->assertEquals('123', $first->id());
		$time = '123456789';
        $first->set_create_time($time, true);
        $first->store();

		$object = objects_first($class, array('create_time' => $time));
        $this->assertNotNull($object);
        $this->assertEquals('123', $object->id());

        $this->assertEquals('real_id_field', $object->id_field());
        $this->assertEquals('real_title_field', $object->title_field());
        $this->assertEquals('bors_tests_object_db6s', $object->table_name());

		// Тестим юникод
		$object = bors_new($class, array(
			'id' => '456',
			'title' => ($pattern = ec('Ещё раз…')),
		));

		$object = bors_find_first($class, array('title' => $pattern));
        $this->assertNotNull($object);

		// Проверим чтение всех объектов. Выше создавалось два
		$objects = bors_find_all($class, array('order' => '-id'));
        $this->assertEquals(2, count($objects));
        $this->assertEquals(456, $objects[0]->id());
    }

	public function setUp()
	{
		$se = call_user_func(array('bors_tests_object_db6', 'storage_engine'));
		$se = object_load($se);
		$se->drop_table('bors_tests_object_db6');
		$se->create_table('bors_tests_object_db6');
	}
}
