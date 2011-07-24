<?php

class bors_lib_admin
{
	static function main_list_properties($class_name)
	{
		$list = bors_lib_object::get_static($class_name, 'main_list_properties');
		if(!$list)
			$list = array('modify_time', 'title', 'id');

		$result = array();
		foreach($list as $property => $data)
		{
			if(is_numeric($property))
			{
				$property = $data;
				$data = array();
			}

			switch($property)
			{
				case 'title':
					set_def($data, 'title', bors_lib_object::get_static($class_name, 'class_title'));
					set_def($data, 'method', 'admin()->imaged_titled_link()');
					break;
				case 'modify_time':
					set_def($data, 'title', ec('Дата изменения'));
					set_def($data, 'func', 'short_time');
					break;
				case 'id':
					set_def($data, 'title', ec('ID'));
					break;
			}

			$result[$property] = $data;
		}

		return $result;
	}
}
