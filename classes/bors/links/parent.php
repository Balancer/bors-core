<?php

class bors_links_parent extends base_object_db
{
	function storage_engine() { return 'bors_storage_mysql'; }

	function replace_on_new_instance() { return true; }

	function table_name() { return 'bors_parents'; }
	function table_fields()
	{
		return array(
			'id',
			'parent_class_name',
			'parent_object_id',
			'child_class_name',
			'child_object_id',
		);
	}

	function auto_targets()
	{
		return array_merge(parent::auto_targets(), array(
			'parent' => 'parent_class_name(parent_object_id)',
			'child'  => 'child_class_name(child_object_id)',
		));
	}

	static function children($object)
	{
		$links = bors_find_all('bors_links_parent', array(
			'parent_class_name' => $object->class_name(),
			'parent_object_id' => $object->id(),
		));

		$children = array();
		foreach($links as $l)
			$children[] = $l->child();

		return $children;
	}

	static function append($parent, $child)
	{
		object_new_instance('bors_links_parent', array(
			'parent_class_name' => $parent->class_name(),
			'parent_object_id' => $parent->id(),
			'child_class_name' => $child->class_name(),
			'child_object_id' => $child->id(),
		));
	}
}
