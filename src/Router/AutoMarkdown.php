<?php

namespace B2\Router;

class AutoMarkdown extends \B2\Router
{
	private $request;

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

	function dispatch($request, $method = 'GET')
	{
		$this->request = $request;

		$path = $request->getUri()->getPath();

		$camel_path = join("\\", array_map('ucfirst', explode('/', rtrim($path, '/'))));

		$camel_path = join('', array_map('ucfirst', explode('-', $camel_path)));

		$reflection = new \ReflectionClass($this->app);
		$namespace = $reflection->getNamespaceName() . "\\";

		$path = $request->getUri()->getPath();

		$prefixes = empty($GLOBALS['B2_COMPOSER']) ? NULL : $GLOBALS['B2_COMPOSER']->getPrefixesPsr4();

		if(!empty($prefixes[$namespace]))
		{
			foreach($prefixes[$namespace] as $class_path)
			{
				$test_path = $class_path . str_replace("\\", '/', $camel_path);
				$view = $this->find_md_tpls_in_dir($test_path, $path);
				if($view)
					return $view;
			}
		}

		foreach($this->app->apps as $reg_app)
		{
			$dir = \bors::$package_app_path[get_class($reg_app)].'/src';

			$app_reflection = new \ReflectionClass($reg_app);
			$app_namespace = $app_reflection->getNamespaceName() . "\\";
			if(!empty($prefixes[$app_namespace]))
			{
				foreach($prefixes[$app_namespace] as $class_path)
				{
					$test_path = $class_path . str_replace("\\", '/', $camel_path);
					$view = $this->find_md_tpls_in_dir($test_path, $path);
					if($view)
						return $view;
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
		$view->storage()->load($view);
		return $view;
	}

	function find_md_tpls_in_dir($dir, $path)
	{
		foreach(glob($dir.'.*') as $file)
		{
			if(preg_match('!^.+/(\w+)\.md.tpl$!', $file, $m))
			{
				$view = new \bors_page_fs_markdown($file);
				$view->set_attr('parents', [dirname($path).'/']);
				return $this->init_view($view);
			}
		}

		foreach(glob($dir.'/Main.*') as $file)
		{
			if(preg_match('!^.+/(\w+\.md.tpl)$!', $file))
			{
				$view = new \bors_page_fs_markdown($file);
				$view->set_attr('parents', [dirname($path).'/']);
				return $this->init_view($view);
			}
		}

		return NULL;
	}
}
