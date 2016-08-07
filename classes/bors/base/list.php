<?php

class base_list extends bors_object_simple
{
	function id_to_name($id)
	{
		$list = $this->named_list();

		return empty($list[$id]) ? NULL : $list[$id];
	}

	function id_to_name_s($id)
	{
		$list = $this->named_list_s();
		return $list[$id];
	}

	function title()
	{
		$names = [];
		foreach(is_array($this->id()) ? $this->id() : [$this->id()] as $id)
			$names[] = $this->id_to_name($id);

		return join($names, ', ');
	}

	function title_s()
	{
		$names = [];
		foreach(is_array($this->id()) ? $this->id() : [$this->id()] as $id)
			$names[] = $this->id_to_name_s($id);

		return join($names, ', ');
	}

	function named_list()
	{
		$res = array();
		foreach($this->valued_list() as $val)
			$res[$val] = $val;

		return $res;
	}

	function zero_item() { return ec('Выберите:'); }
	function named_list_zero() { return array_merge(array(0 => $this->zero_item()), $this->named_list()); }

	static function make($class_name, $where = array(), $data = array())
	{
		return bors_legacy::make_base_list($class_name, $where, $data);
	}

	function __toString()
	{
		if($t = $this->title())
			return $t;

		return '['.$this->id().']';
	}
}
