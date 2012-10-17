<?php

class bors_moderator_note extends ors_page_db
{
	function db_name() { return config('main_bors_db'); }
	function table_name() { return 'moderator_notes'; }
	function table_fields()
	{
		return array(
			'id',
			'create_time',
			'user_id',
			'moderator_id',
			'target_class_id',
			'target_object_id',
			'comment',
		);
	}
}
