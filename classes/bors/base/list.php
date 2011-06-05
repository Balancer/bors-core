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

	function zero_item() { return ec('Выберите:'); }
	function named_list_zero() { return array_merge(array(0 => $this->zero_item()), $this->named_list()); }

	static function make($class_name, $where = array())
	{
		$list = array(0 => '');
		foreach(objects_array($class_name, array_merge(array('order' => 'title'), $where)) as $x)
			if($x->id() && ($t = $x->title()))
				$list[$x->id()] = $t;

		return $list;
	}
}
