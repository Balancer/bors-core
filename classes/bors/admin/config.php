<?php

class bors_admin_config extends base_config
{
	function config_data()
	{
		$data = parent::config_data();
		$data['access_engine'] = config('admin_access_default');
		if($class_name = object_property($this->id(), 'main_class'))
		{
			$data['new_sublink'] = object_property($this->id(), 'admin_group_url').'new/';
			if(class_exists($class_name))
			{
				$foo = new $class_name(NULL);
				$data['new_title'] = ec('Добавить ').$foo->get('class_title_vp');
			}
		}

		$data['template'] = config('admin_template', 'default');

		return $data;
	}

	function page_data()
	{
		return array_merge(parent::page_data(), array(
			'default_right_menu' => config('admin_right_menu', 'xfile:bors/admin/right-menu.html'),
		));
	}
}
