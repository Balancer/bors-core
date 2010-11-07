<?php

class bors_lib_object
{
	static function tree_map($object)
	{
		$parent_id = $object->parent_id();
		if(!$parent_id)
			return '';

		$parent = $object->parent();
		if(!$parent)
		{
			debug_hidden_log('errors-structure', "Can't load parent '{$parent_id}' for {$object}");
			return '';
		}

		return self::tree_map($parent).$parent_id.'.';
	}
}
