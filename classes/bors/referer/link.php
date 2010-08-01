<?php

class bors_referer_link extends base_object_db
{
	function main_table() { return 'bors_referer_links'; }

	function main_table_fields()
	{
		return array(
			'id',
			'create_time',
			'modify_time',
			'referer_normalized_url',
			'target_class_name',
			'target_object_id',
			'count',
			'target_url',
			'referer_original_url',
			'comment',
		);
	}

	function replace_on_new_instance() { return true; }

	function auto_targets() { return array('target' => 'target_class_name(target_object_id)'); }
}
