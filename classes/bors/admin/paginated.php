<?php

class bors_admin_paginated extends bors_paginated
{
	function _config_class_def() { return config('admin_config_class'); }

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

	function _is_admin_list_def() { return true; }
}
