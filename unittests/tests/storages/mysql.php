<?php

class storages_mysql_unittest_object extends base_object_db
{
	function db_name() { return config('unit-test.mysql.db'); }
	function table_name() { return 'storages_mysql_unittest_objects'; }
	function storage_engine() { return 'bors_storage_mysql'; }

	function table_fields()
	{
		return array(
			'title',
			'text',
			'c_id',
		);
	}
}

class storages_mysql_unittest extends PHPUnit_Framework_TestCase
{
    public function test()
    {
		$db = new driver_mysql(config('unit-test.mysql.db'));
		$db->query('DROP TABLE IF EXISTS storages_mysql_unittest_objects;');
		$cls = 'storages_mysql_unittest_object';

		$x = bors_new($cls, array(
			'title' => 'Заголовок',
			'text' => 'Текст',
			'c_id' => 123,
		));

		$this->assertNotNull($x);
		$this->assertEquals(1, $x->id());

		$x = bors_find_first($cls, array('c_id' => 123));
		$this->assertNotNull($x);
		$this->assertEquals('Текст', $x->text());
	}
}
