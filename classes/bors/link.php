<?php

class bors_link extends base_object_db
{
	private $replace = false;

	function main_table() { return 'bors_cross'; }
	function main_table_fields()
	{
		return array(
			'id',
			'type_id',
			'from_class',
			'from_id',
			'to_class',
			'to_id',
			'sort_order',
			'create_time',
			'modify_time',
			'owner_id',
			'comment',
		);
	}

	function from_object() { return object_load($this->from_class(), $this->from_id()); }
	function to_object()   { return object_load($this->to_class(),   $this->to_id()); }
	function type() { return object_load('bors_cross_types', $this->type_id()); }

	function set_from($obj_from, $db_up)
	{
		$this->set_from_class($obj_from->class_id(), $db_up);
		$this->set_from_id   ($obj_from->id(), $db_up);
	}

	function set_to($obj_to, $db_up)
	{
		$this->set_to_class($obj_to->class_id(), $db_up);
		$this->set_to_id   ($obj_to->id(), $db_up);
	}

	function set_replace($bool) { $this->replace = $bool; }
	function replace_on_new_instance() { return $this->replace; }

	static function link_objects($obj_from, $obj_to, $params = array())
	{
		$link = object_new('bors_link');
		$link->set_from($obj_from, true);
		$link->set_to($obj_to, true);

		foreach($params as $k => $v)
			$link->{"set_$k"}($v, true);

		$link->new_instance();
		$link->store();
	}
}
