<?php

class bors_votes_thumb extends base_object_db
{
	function main_table() { return 'bors_thumb_votes'; }
	function main_table_fields()
	{
		return array(
			'id',
			'user_id',
			'target_class_name',
			'target_object_id',
			'target_user_id',
			'score',
			'create_time',
		);
	}

	function replace_on_new_instance() { return true; }
}
