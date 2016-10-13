<?php

namespace B2\Router;

class RegApps
{
	private $app;

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
	}

	function dispatch($request)
	{
		$this->request = $request;

		$path = $request->getUri()->getPath();

		foreach($this->app->apps as $reg_app)
		{
			if($map = $reg_app->get('url_map'))
			{
				require_once $map;
			}

			$map = $reg_app->route_map();

			foreach($map as $map_path => $map_class)
			{
				$base = $reg_app->base_path();
				$map_pattern = '/^'.str_replace('/', "\\/", $base.$map_path).'/';
//				echo "pattern=[$map_pattern]; path=[$path]<br/>";
				if(preg_match($map_pattern, $path, $m))
				{
					$view = \bors::load($map_class, NULL);
					return $this->init_view($view);
				}
			}
		}

		return NULL;
	}

	function init_view($view)
	{
		$view->b2_configure();
		bors()->set_main_object($view);
		$view->set_request($this->request);
		$view->set_attr('called_url', (string)$this->request->getUri());
		if($storage = $view->storage())
			$storage->load($view);
		return $view;
	}
}
