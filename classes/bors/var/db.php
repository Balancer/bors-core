<?php

class bors_var_db extends base_object_db
{
	function can_cached() { return false; }
	function storage_engine() { return 'bors_storage_mysql'; }
	function db_name() { return config('bors_core_db'); }
	function table_name() { return 'bors_server_vars'; }

	function table_fields()
	{
		return array(
			'id',
//			'container_class_name',
//			'container_id',
			'name',
			'title',
			'value',
			'type',
			'create_time',
			'modify_time',
			'expire_time',
		);
	}

	function replace_on_new_instance() { return true; }
}
