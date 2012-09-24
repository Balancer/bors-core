<?php

class bors_admin_tools_setdefault extends bors_admin_page
{
	function config_class() { return config('admin_config_class'); }

	function pre_parse()
	{
		$object = object_load($_GET['object']);
		if(empty($_GET['image']))
		{
			$target_id = $_GET['target_id'];
			$target_field = $_GET['target_field'];
			if(!preg_match('/^\w+$/', $target_field))
				return bors_message(ec('Некорректное имя поля ').$target_field);
			
			$object->set($target_field, intval($target_id), true);
		}
		else
		{
			$image = object_load($_GET['image']);
			$object->set_default_image_id($image->id(), true);
		}

		return go_ref($object->admin_url());
	}
}
