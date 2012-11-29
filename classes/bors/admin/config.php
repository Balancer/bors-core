<?php

class bors_admin_config extends bors_config
{
	function object_data()
	{
		static $recurse = false;
		if($recurse)
			return array();

		$recurse = true;

		$data = parent::object_data();
		$data['access_engine'] = config('admin_access_default');
		if($class_name = object_property($this->id(), 'main_class'))
		{
			$data['new_sublink'] = object_property($this->id(), 'admin_group_url').'new/';
			if(class_exists($class_name))
			{
				if(bors_load_uri($data['new_sublink']))
					$data['new_title'] = ec('Добавить ').bors_foo($class_name)->get('class_title_vp');
				else
					unset($data['new_sublink']);
			}
		}

		$data['template'] = config('admin_template', 'default');

		$recurse = false;
		return $data;
	}

	function page_data()
	{
		return array_merge(parent::page_data(), array(
			'default_right_menu' => config('admin_right_menu', 'xfile:bors/admin/right-menu.html'),
		));
	}
}
