<?php

class bors_legacy_52
{
	static function make_base_list($class_name, $where = array(), $data = array())
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

		// Возможность задать произвольный формат текста элемента списка
		// Используется в ucrm/company/business/entity.yaml
		// list_fields_format: '%title%%qshort%'
		// qshort(): '$this->short() ? " (".$this->short().")" : ""'

		// На какое говно не пойдёшь ради совместимости с 5.2
		//TODO: снести нахрен, когда 5.2 нигде не останется
		global $___list_make_52_x;
		foreach(bors_find_all($class_name, array_merge(array('order' => $order), $where)) as $___list_make_52_x)
			if($___list_make_52_x->id() && ($t = preg_replace_callback('/(%(\w+)%)/',  'list_make_52_helper', $format)))
				$list[$___list_make_52_x->id()] = $t;

		return $list;
	}

	static private function list_make_52_helper($m)
	{
		global $___list_make_52_x;
		return $___list_make_52_x->get($m[2]);
	}
}
