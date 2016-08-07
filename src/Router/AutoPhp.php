<?php

namespace B2\Router;

class AutoPhp
{
	private $request;
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

		$camel_path = join("\\", array_map('ucfirst', explode('/', rtrim($path, '/'))));

		$camel_path = join('', array_map('ucfirst', explode('-', $camel_path)));

		$reflection = new \ReflectionClass($this->app);
		$namespace = $reflection->getNamespaceName();
		$base_class =  $namespace . $camel_path;

		if(class_exists($base_class))
		{
//			$view = $base_class::load(NULL);
			$view = new $base_class(NULL); // Now simple direct load.
			if($view->get('route') == 'auto')
			{
				$view->set_attr('parents', [dirname($path).'/']);
				return $this->init_view($view);
			}
		}

		if(class_exists($cn = $base_class.'\\Main'))
		{
			$view = new $cn(NULL); // Now simple direct load.
			if($view->get('route') == 'auto')
			{
				$view->set_attr('parents', [dirname($path).'/']);
				return $this->init_view($view);
			}
		}

		if(preg_match('/\\\\(\d+)$/', $base_class, $m))
		{
			$object_id = $m[1];
			$check_class_name = preg_replace('/\\\\(\d+)$/', '\\Edit', $base_class);
			if(class_exists($check_class_name))
			{
				$view = bors_load($check_class_name, $object_id);
				if($view->get('route') == 'auto')
				{
					$view->set_attr('parents', [dirname($path).'/']);
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
		return $view;
	}
}
