<?php

class bors_tests_storage_mysql_db extends base_object_db
{
	function storage_engine() { return 'bors_storage_mysql'; }
	function db_name() { return config('unit-test.mysql.db'); }
	function table_name() { return 'object_db_mysql'; }
	function table_fields()
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
