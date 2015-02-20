<?php

class bors_legacy_53
{
	static function make_base_list($class_name, $where = array(), $data = array())
	{
		if(empty($data['have_null']))
			$list = array(0 => '');
		else
			$list = array(NULL => '');

		if(!empty($data['non_empty']))
			$list = array();

		if(empty($class_name))
			$class_name = defval($data, 'main_class');

		if(empty($class_name))
		{
			bors_debug::syslog("classes-warning", "Try to make list without name");
			return array();
		}

//		echo debug_trace();
		$foo = new $class_name(NULL);
		$order = $foo->get('list_fields_sort', 'title');

		$format = $foo->get('list_fields_format', '%title%');

		// Возможность задать произвольный формат текста элемента списка
		// Используется в ucrm/company/business/entity.yaml
		// list_fields_format: '%title%%qshort%'
		// qshort(): '$this->short() ? " (".$this->short().")" : ""'

		if(method_exists($class_name, 'storage'))
		{
			foreach(bors_find_all($class_name, array_merge(array('order' => $order), $where)) as $x)
			{
				if($x->id() && ($t = preg_replace_callback('/(%(\w+)%)/', function($m) use ($x) { return $x->get($m[2]); }, $format)))
					$list[$x->id()] = $t;
			}
		}
		if(method_exists($class_name, 'named_list'))
		{
			$list = bors_foo($class_name)->named_list();
		}

		return $list;
	}
}
