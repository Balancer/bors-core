<?php

class bors_tests_object_db1 extends base_object_db
{
	function storage_engine() { return 'storage_db_mysql_smart'; }
	function main_db() { return config('unit-test.mysql.db'); }
	function main_table() { return 'object_db1'; }
	function fields()
	{
		return array($this->main_db() => array($this->main_table() => array(
			'id' => 'topic_id',
			'title',
			'create_time' => array('type' => 'int'),
			'modify_time' => array('type' => 'int'),
			'owner_id',
		)));
	}
}
