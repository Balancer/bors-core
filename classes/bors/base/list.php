<?php

class base_list extends base_empty
{
	function id_to_name($id)
	{
		$list = $this->named_list();
		return $list[$id];
	}

	function id_to_name_s($id)
	{
		$list = $this->named_list_s();
		return $list[$id];
	}
	
	function title() { return $this->id_to_name($this->id()); }
	function title_s() { return $this->id_to_name_s($this->id()); }

	function named_list()
	{
		$res = array();
		foreach($this->valued_list() as $val)
			$res[$val] = $val;

		return $res;
	}

	static function factory($id = NULL) { return object_load(self::get_class_static(1), $id); }
}
