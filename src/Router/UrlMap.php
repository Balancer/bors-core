<?php

namespace B2\Router;

class UrlMap
{
	var $base_url = '/';

	static function instance($base_url = '/')
	{
		static $instance = NULL;

		if($instance)
			return $instance;

		$instance = call_user_func(get_called_class());
		$instance->base_url = $base_url;
		return $instance;
	}

	function map_load($package_name)
	{
	}
}
