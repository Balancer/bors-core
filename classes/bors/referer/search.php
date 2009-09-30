<?php

class bors_referer_search extends base_object_db
{
	function main_table_fields()
	{
		return array(
			'id',
			'create_time',
			'modify_time',
			'query',
			'target_class_name',
			'target_object_id',
			'count',
			'search_engine',
			'target_url',
			'search_url',
			'comment',
		);
	}
	function replace_on_new_instance() { return true; }

	function object() { return $this->__havec('object') ? $this->__lastc() : $this->__setc(object_load($this->target_class_name(), $this->target_object_id())); }
}
