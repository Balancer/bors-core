<?php

class bors_admin_objects_list extends bors_admin_meta_main
{
	function main_class() { return $this->id(); }

	function parents() { return array(bors_lib_object::get_static($this->main_class(), 'admin_parent_url')); }

	function nav_name() { return bors_lower($this->title()); }

	function body_data()
	{
		$list = bors_lib_admin::main_list_properties($this->main_class());
		$headers = array();

		foreach($list as $property => $data)
			$headers[] = popval($data, 'title', $property);

		$objects = array();
		foreach($this->items() as $x)
		{
			$values = array();
			foreach($list as $property => $data)
			{
				if(empty($data['method']))
					$v = $x->get($property);
				else
				{
					if(preg_match('/^\s+$/', $data['method']))
						$v = $x->get($data['method']);
					else
						eval("\$v = \$x->{$data['method']};");
				}

				if(!empty($data['func']))
					$v = $data['func']($v);
				$values[] = $v;
			}
			$objects[] = $values;
		}

		return compact('headers', 'objects');
	}
}
