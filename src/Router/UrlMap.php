<?php

namespace B2\Router;

class UrlMap
{
	var $app;
	var $route_map = [];
	var $request;

	function __construct($app)
	{
		$this->app = $app;
	}

	static function instance($app)
	{
		static $instance = NULL;

		if($instance)
			return $instance;

		$class_name = get_called_class();
		$instance = new $class_name($app);

		return $instance;
	}

	function init()
	{
		$this->map_load();
	}

	function map_load()
	{
		global $bors_data;

		$app_class = $this->app->class_name();
		$path = @\bors::$package_app_path[$app_class];
		if($path && file_exists($path.'/url_map.php'))
		{
			$GLOBALS['b2']['side']['router'] = $this;
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

	function dispatch($request)
	{
		$this->request = $request;

		// $request in PSR-7
		// (string)$request->getUri() -> full uri with scheme://host/path/...
		// $request->getUri()->getPath() = string

		$path = $request->getUri()->getPath();
		foreach($this->route_map as $s)
		{
			if(!preg_match('/^(\S+)\s+=>\s+(.+)$/', $s, $m))
				throw new Exception(_("Incorred route format for UrlMap: ".$s));

			$pattern    = str_replace('!', '\!', $m[1]);
			$class_name = $m[2];

			if(!preg_match('!^'.$pattern.'$!', $path, $url_match))
				continue;

			if(preg_match('/^\w+$/', $class_name)
					&& ($view = \bors::load($class_name, NULL)))
				return $this->init_view($view);

			if(preg_match('/^(\w+)\((\d+)\)$/', $class_name, $m)
					&& ($view = \bors::load($m[1], $url_match[$m[2]])))
				return $this->init_view($view);

			if(preg_match('/^(\w+)\(url\)$/', $class_name, $m)
					&& ($view = \bors::load($m[1], (string)$request->getUri())))
				return $this->init_view($view);

//			\bors_debug::syslog('warning-url-map', _("Unknown route class name format for UrlMap").": [{$s}]");
//			throw new \Exception(_("Unknown route class name format for UrlMap").": [{$s}]");
		}

		return NULL;
	}

	function init_view($view)
	{
		$view->set_request($this->request);

		return $view;
	}
}
