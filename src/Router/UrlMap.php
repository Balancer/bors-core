<?php

namespace B2\Router;

class UrlMap
{
	var $app;
	var $route_map = [];

	function __construct($app)
	{
		$this->app = $app;
	}

	static function instance($app)
	{
		static $instance = NULL;

		if($instance)
			return $instance;

		$instance = call_user_func(get_called_class());

		$instance->app = $app;

		return $instance;
	}

	function map_load()
	{
		global $bors_data;

		$app_class = $this->app->class_name();
		r($app_class);
		$path = @bors::$package_app_path[$app_class];
		if($path && file_exists($path.'/url_map.php'))
		{
			$GLOBALS['b2']['side']['app'] = $this->app;
			$map = [];
			require_once $path.'/url_map.php';
			if(!empty($map))
				$this->map_register($map);
		}
	}

	function map_register($route_map)
	{
		$this->route_map = array_merge($this->route_map, $route_map);
	}
}
