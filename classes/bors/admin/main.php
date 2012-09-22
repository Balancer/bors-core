<?php

class bors_admin_main extends bors_page
{
	function title() { return ec('Управление системой BORS©'); }
	function nav_name() { return ec('администрирование'); }
	function can_cache() { return false; }
	function admin() { return false; }

	function template()
	{
		if(!bors()->user() && ($tpl = config('admin_login_template')))
			return $tpl;

		return config('admin_template', 'default');
	}
}
