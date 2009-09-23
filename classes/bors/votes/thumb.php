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

	function owner()  { return $this->load_attr('owner',  object_load('forum_user', $this->user_id())); }
	function object() { return $this->load_attr('object', object_load($this->target_class_name(), $this->target_object_id())); }
	function target() { return $this->load_attr('target', object_load($this->target_class_name(), $this->target_object_id())); }
}
