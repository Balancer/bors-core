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

	static function make($class_name, $where = array(), $data = array())
	{
		if(empty($data['have_null']))
			$list = array(0 => '');
		else
			$list = array(NULL => '');

		if(!empty($data['non_empty']))
			$list = array();

		$foo = new $class_name(NULL);
		$order = $foo->get('list_fields_sort', 'title');

		$format = $foo->get('list_fields_format', '%title%');

		foreach(bors_find_all($class_name, array_merge(array('order' => $order), $where)) as $x)
			if($x->id() && ($t = preg_replace_callback('/(%(\w+)%)/', function($m) use ($x) { return $x->get($m[2]); }, $format )))
				$list[$x->id()] = $t;

		return $list;
	}

	function __toString()
	{
		if($t = $this->title())
			return $t;

		return '['.$this->id().']';
	}
}
