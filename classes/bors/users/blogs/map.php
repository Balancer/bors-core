<?php

class bors_users_blogs_map extends base_object_db
{
	function storage_engine() { return 'bors_storage_mysql'; }
	function db_name() { return 'AB_BORS'; }
	function table_name() { return 'external_blogs_maps'; }
	function table_fields()
	{
		return array(
			'id',
			'target_class_id' => 'class_id',
			'target_object_id' => 'object_id',
			'blog_class_id' => 'blog_type_id',
			'blog_object_id',
		);
	}
}
