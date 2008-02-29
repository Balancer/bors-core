<?php
	function smarty_modifier_bors_list_item_name($item, $class_name)
	{
		$list = class_load($class_name);
		if(!$list)
			return "<b>{$item}</b> <small>Can't load class {$class_name} in ".__FILE__."</small>";
		return $list->id_to_name($item);
	}
