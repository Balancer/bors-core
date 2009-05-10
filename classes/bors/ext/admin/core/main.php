<?php

// config_set('default_template', 'default/simple.html');

class bors_ext_admin_core_main extends base_page
{
	//TODO: добавить access
	function title() { return ec("Редактор ядра"); }

	function body_engine() { return 'body_lcmlh'; }
	function acl_edit_sections() { return array('*' => 10); }

	function on_action_edit($data)
	{
		return go('/___/core/edit/?object='.@$data['_edit_class_name']);
	}
}
