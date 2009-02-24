<?php

class bors_admin_tools_setdefault extends base_page
{
	function config_class() { return config('admin_config_class'); }

	function pre_parse()
	{
		$object = object_load($_GET['object']);
		$image = object_load($_GET['image']);

		$object->set_default_image_id($image->id(), true);

		return go_ref($object->admin_url());
	}
}
