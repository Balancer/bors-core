<?php

class bors_users_blog extends base_object_db
{
	function storage_engine() { return 'bors_storage_mysql'; }
	function db_name() { return 'AB_BORS'; }
	function table_name() { return 'external_blogs'; }
	function table_fields()
	{
		return array(
			'id',
			'user_id' => 'bors_user_id',
			'blog_class' => 'blog',
			'login',
			'password',
			'is_active' => 'active',
		);
	}
}
