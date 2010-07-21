<?php

// Вариант объекта с DB по умолчанию.

class bors_tests_object_db3 extends base_object_db
{
	function storage_engine() { return 'storage_db_mysql_smart'; }

	function main_table() { return 'object_db3'; }

	function main_table_fields()
	{
		return array(
			'id' => 'real_id_field',
			'title' => 'real_title_field',
			'create_time',
			'modify_time',
			'owner_id',
		);
	}
}
