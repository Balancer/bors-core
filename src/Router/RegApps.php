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

		foreach($this->app->apps as $reg_app)
		{
			if($map = $reg_app->get('url_map'))
			{
				require_once $map;
			}
		}

		$path = $request->getUri()->getPath();

		return NULL;
	}

	function init_view($view)
	{
		$view->b2_configure();
		bors()->set_main_object($view);
		$view->set_request($this->request);
		$view->set_attr('called_url', (string)$this->request->getUri());
		$view->storage()->load($view);
		return $view;
	}
}
