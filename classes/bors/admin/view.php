<?php

class bors_admin_view extends bors_view
{
	function config_class() { return config('admin_config_class', 'bors_admin_config'); }

	function access()
	{
		$access = $this->access_engine();
		if(!$access)
			$access = config('admin_access_default');

		return bors_load($access, $this);
	}

	function pre_show()
	{
		template_nocache();	// Админ-страницы не кешируются
		template_noindex();	// Админ-страницы не индексируются

		return parent::pre_show();
	}

	function _project_name_def() { return bors_core_object_defaults::project_name($this); }
	function _section_name_def() { return bors_core_object_defaults::section_name($this); }
	function _config_class_def() { return bors_core_object_defaults::config_class($this); }
}
