<?php

class loaders_yaml_unittest extends PHPUnit_Framework_TestCase
{
    public function test()
    {
		$db = new driver_mysql(config('unit-test.mysql.db'));
		$db->query('DROP TABLE IF EXISTS bors_tests_yaml_test1s;');

		$cls = 'bors_tests_yaml_test1';

		$x = bors_new($cls, array(
			'title' => 'Заголовок',
			'text' => 'Текст',
		));

		$this->assertNotNull($x);
		$this->assertEquals(1, $x->id());

		$x = bors_find_first($cls, array('text' => 'Текст'));
		$this->assertNotNull($x);
		$this->assertEquals('Заголовок', $x->title());

		$db->query('DROP TABLE IF EXISTS yaml_test2;');

		$cls = 'bors_tests_yaml_test2';

		$x = bors_new($cls, array(
			'title' => 'Заголовок2',
			'text' => 'Текст2',
		));

		$this->assertNotNull($x);
		$this->assertEquals(1, $x->id());

		$x = bors_find_first($cls, array('text' => 'Текст2'));
		$this->assertNotNull($x);
		$this->assertEquals('Заголовок2', $x->title());
	}
}
