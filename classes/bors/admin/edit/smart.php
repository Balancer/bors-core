<?php

class bors_admin_edit_smart extends base_page
{
	function config_class() { return config('admin_config_class'); }

	function parents() { return array($this->id()); }
	
	function title() { return ec('редактор'); }

	function object() { return object_load(urldecode($this->id())); }
	function fields() { return explode(',', $this->args('fields')); }

	function local_template_data_set()
	{
		return array(
			'object' => $this->object(),
			'fields' => $this->fields(),
			'referer' => ($ref = bors()->referer()) ? $ref : 'newpage_admin',
		);
	}

//	function url() { return '/admin/edit-smart/?object='.$this->object()->internal_uri(); }
}
