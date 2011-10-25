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
			$foo = new $class_name(NULL);
			$data['new_title'] = ec('Добавить ').$foo->get('class_title_vp');
		}

//		'template' => config('admin_template', 'default'),

		return $data;
	}

	function template_data()
	{
		return array_merge(parent::template_data(), array(
			'default_right_menu' => config('admin_right_menu', 'xfile:bors/admin/right-menu.html'),
		));
	}
}
