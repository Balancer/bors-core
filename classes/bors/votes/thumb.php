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

	function object() { return $this->target(); }
	function target() { return $this->__havec('target') ? $this->__lastc() : $this->__setc(object_load($this->target_class_name(), $this->target_object_id())); }

	function auto_objects()
	{
		return array(
			'owner' => 'forum_user(user_id)',
			'target_user' => 'balancer_board_user(target_user_id)',
		);
	}
}
