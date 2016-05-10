<?php

namespace B2\Router;

class AutoMarkdown
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
		$namespace = $reflection->getNamespaceName() . "\\";

		$prefixes = empty($GLOBALS['B2_COMPOSER']) ? NULL : $GLOBALS['B2_COMPOSER']->getPrefixesPsr4();
		if(!empty($prefixes[$namespace]))
		{
			foreach($prefixes[$namespace] as $class_path)
			{
				$test_path = $class_path.str_replace("\\", '/', $camel_path);

				foreach(glob($test_path.'.*') as $file)
				{
					if(preg_match('!^.+/(\w+\.md.tpl)$!', $file))
					{
						$view = new \bors_page_fs_markdown($file);
						$view->set_attr('parents', [dirname($path).'/']);
						return $this->init_view($view);
					}
				}

				foreach(glob($test_path.'/Main.*') as $file)
				{
					if(preg_match('!^.+/(\w+\.md.tpl)$!', $file))
					{
						$view = new \bors_page_fs_markdown($file);
						$view->set_attr('parents', [dirname($path).'/']);
						return $this->init_view($view);
					}
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
		$view->storage()->load($view);
		return $view;
	}
}
