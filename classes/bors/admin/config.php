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

		set_def($data, 'side_menu', array());

		if($this->get('new_sublink') || $this->id()->get('real_object'))
		{
			set_def($data['side_menu'], 'Действия', array());

			//<li class="b{$new_object_type}"><a href="{$this->get('new_sublink')}">{$this->get('new_title')}</a></li>{/if}
			if($this->get('new_sublink'))
			{
				$data['side_menu']['Действия'][] = array(
					'url' => $this->get('new_sublink'),
					'title' => $this->get('new_title'),
				);
			}

			if($this->id()->get('real_object'))
			{
				$data['side_menu']['Действия'][] = array(
					'url' => $this->id()->real_object()->url(),
					'title' => 'Посмотреть на сайтe',
					'type' => 'view',
					'link_target' => '_blank',
				);
			}
		}

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
