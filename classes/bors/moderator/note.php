<?php

class bors_moderator_note extends base_page_db
{
	function main_db() { return config('main_bors_db'); }
	function main_table() { return 'moderator_notes'; }
	function main_table_fields()
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
