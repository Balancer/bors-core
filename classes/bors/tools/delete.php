<?php

class bors_tools_delete extends base_page
{
	function config_class() { return 'admin_config'; }

	function parents() { return array($this->id()); }
	
	function title() { return ec('удаление'); }

	function object() { return object_load($this->id()); }

	function acl_edit_sections() { return array('*' => 3); }
	
	function on_action_delete()
	{
		$this->object()->delete();
		return go($_GET['ref']);
	}
}
