<?php

// Вариант объекта с DB по умолчанию и новыми именами методов.

class bors_tests_object_db4 extends base_object_db
{
	function storage_engine() { return 'storage_db_mysql_smart'; }

	function table_name() { return 'object_db4'; }

	function fields_map()
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
