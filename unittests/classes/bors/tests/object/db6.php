<?php

class bors_tests_object_db6 extends base_object_db
{
	function storage_engine() { return 'bors_storage_mysql'; }

	function table_fields()
	{
		return array(
			'id' => 'real_id_field',
			'title' => 'real_title_field',
			'create_time', // => 'UNIX_TIMESTAMP(`create_time`)',
			'modify_time',
			'owner_id',
		);
	}
}
