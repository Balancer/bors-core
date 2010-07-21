<?php

class bors_tests_object_db2_unittest extends PHPUnit_Framework_TestCase
{
    public function testObject()
    {
		$object = object_load('bors_tests_object_db2', -12345);
        $this->assertNull($object);
		$object = object_new_instance('bors_tests_object_db2', array(
			'id' => '123',
			'title' => 'test',
			'additional' => 'info',
			'topic_id' => 123456,
			'html_raw' => "qwe\\'rty",
		));
		$object->store();
        $this->assertNotNull($object);

		$first = objects_first('bors_tests_object_db2', array('title' => 'test'));
        $this->assertNotNull($first);
        $this->assertEquals('123', $first->id());
        $this->assertEquals('info', $first->additional());
		$time = '123456789'; // Не менять! Дальше привязка форматов к этому значению!
        $first->set_create_time($time, true);
        $first->store();

		$object = objects_first('bors_tests_object_db2', array('create_time' => $time));
        $this->assertNotNull($object);
        $this->assertEquals('123', $object->id());

		// Проверяем на корректность разворачивания имён привязок вторичных таблиц.
		// в БД поле tid, а свойство - topic_id:
		$topic = objects_first('bors_tests_object_db2', array('topic_id' => 123456));
        $this->assertNotNull($topic);
        $this->assertEquals('123456', $object->topic_id());

		// Проверяем работу функций mysql в полях вида date => 'FROM_UNIXTIME(time)'
		$topic->set_time($time, true);
		$topic->store();
		$topic = objects_first('bors_tests_object_db2', array('time' => $time));
        $this->assertNotNull($topic);

		// Заодно проверим функцию генерации времени mysql-формата
		require_once('inc/datetime.php');
        $this->assertEquals(date_format_mysqltime($time), "'".$topic->date()."'");

		// Теперь проверка обратных функций в PHP, типа 
        $this->assertEquals('25f9e794323b453885f5181f1b624d0b', $topic->date_md5());

        $this->assertEquals("qwe'rty", $topic->html());

		$topic->set_html("as'df", true);
		$topic->store();
		$mytime = date_format_mysqltime($time);
		$topic = objects_first('bors_tests_object_db2', array('date' => substr($mytime, 1, strlen($mytime)-2)));
        $this->assertNotNull($topic);
        $this->assertEquals("as\\'df", $topic->html_raw());

		// Проверка на удаление
		$topic->delete(false);
		$x = object_load('bors_tests_object_db2', 123);
        $this->assertNull($x);
    }

	public function setUp()
	{
		$se = call_user_func(array('bors_tests_object_db2', 'storage_engine'));
		$se = object_load($se);
		$se->drop_table('bors_tests_object_db2');
		$se->create_table('bors_tests_object_db2');
	}
}
