<?php

class bors_tests_object_db extends base_object_db
{
	function storage_engine() { return 'storage_db_mysql_smart'; }
	function main_db() { return config('unit-test.mysql.db'); }
	function main_table() { return 'object_db'; }
	function main_table_fields()
	{
		return array(
			'id',
			'title',
			'create_time',
			'modify_time',
			'owner_id',
		);
	}
}
