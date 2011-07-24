<?php

class bors_admin_meta_main extends bors_paginated
{
	function config_class() { return config('admin_config_class'); }
	function access_name() { return call_user_func(array($this->main_admin_class(), 'access_name')); }

	function title() { return ec('Управление ').bors_lib_object::get_static($this->main_class(), 'class_title_tpm'); }
}
