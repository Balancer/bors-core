<?php

class bors_admin_tools_setsortorder extends base_page
{
	function config_class() { return config('admin_config_class'); }

	function pre_parse()
	{
		$child = object_load($_GET['child']);
		$parent = empty($_GET['parent']) ? NULL : object_load($_GET['parent']);

		if($parent) // Если в параметрах указан parent, то это cross.
			bors_link::link_objects($parent, $child, array(
				'sort_order' => @$_GET['sort_order'],
				'replace' => true,
				'type_id' => 5,
			));
//			$parent->add_cross_object($child, $_GET['sort_order']);
		else
			$child->set_sort_order($_GET['sort_order'], true);

		$parent = $child->parent_object();
		return go_ref($parent ? $parent->admin_url() : NULL);
	}
}
