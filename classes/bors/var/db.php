<?php

class bors_var_db extends base_object_db
{
	function can_cached() { return false; }
	function main_table() { return 'bors_server_vars'; }

	function main_table_fields()
	{
		return array(
			'id',
			'name',
			'value',
			'create_time',
			'modify_time',
			'expire_time',
		);
	}
	
	function replace_on_new_instance() { return true; }
}
