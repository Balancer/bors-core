<?php

class bors_attach extends base_object_db
{
	function storage_engine() { return 'bors_storage_mysql'; }
	function table_name() { return 'bors_attaches'; }
	function table_fields()
	{
		return array(
			'id',
			'title',
			'mime_type',
			'full_file_name',
			'original_name',
			'parent_class_name',
			'parent_id',
			'size',
			'modify_time',
			'create_time',
			'owner_id',
			'last_editor_id',
		);
	}
}
