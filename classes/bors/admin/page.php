<?php

class bors_admin_page extends bors_page
{
	function config_class() { return config('admin_config_class'); }

	function pre_show()
	{
		template_nocache();	// Админ-страницы не кешируются
		template_noindex();	// Админ-страницы не индексируются

		return parent::pre_show();
	}
}
