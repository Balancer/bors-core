<?php

class bors_class_name extends bors_object_db
{
	static function id_prepare($id)
	{
		if(!is_numeric($id))
			$id = class_name_to_id($id);

		return parent::id_prepare($id);
	}

	function table_name() { return 'bors_class_names'; }
	function table_fields() { return array('id', 'name'); }

	function title()
	{
		$cn = $this->name();
		return @call_user_func(array($cn, 'class_title'));
	}
}
