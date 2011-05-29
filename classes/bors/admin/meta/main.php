<?php

class bors_admin_meta_main extends bors_page
{
	function config_class() { return config('admin_config_class'); }
	function access_name() { return call_user_func(array($this->main_admin_class(), 'access_name')); }
}
