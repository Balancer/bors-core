<?php

namespace B2;

class Project extends \bors_project
{
	static $routers = array();

	function regRouter($router_class, $base_url = '', $domain = '')
	{
		$router = call_user_func(array($router_class, 'factory'));
		$router->routes_init($base_url, $domain);
		self::$routers[$domain][] = $router;
		return $this;
	}

	function route_view($request)
	{
		// (string)->getUri() -> full uri with scheme://host/path/...
		// ->getUri()->getPath() = string
		$path = $request->getUri()->getPath();

		$camel_path = join("\\", array_map('ucfirst', explode('/', rtrim($path, '/'))));

		$camel_path = join('', array_map('ucfirst', explode('-', $camel_path)));

//		r($camel_path);

		$reflection = new \ReflectionClass($this);
		$namespace = $reflection->getNamespaceName();
		$base_class =  $namespace . $camel_path;

//		r($namespace);

		$view = NULL;

		if(class_exists($base_class))
		{
//			$view = $base_class::load(NULL);
			$view = new $base_class(NULL); // Now simple direct load.
			$view->b2_configure();
			if($view->get('route') == 'auto')
				return $view;
		}

		$namespace .= "\\";

		$prefixes = $GLOBALS['B2_COMPOSER']->getPrefixesPsr4();
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
						$view->b2_configure();
						$view->storage()->load($view);
					}
				}

				foreach(glob($test_path.'/Main.*') as $file)
				{
					if(preg_match('!^.+/(\w+\.md.tpl)$!', $file))
					{
						$view = new \bors_page_fs_markdown($file);
						$view->b2_configure();
						$view->storage()->load($view);
					}
				}
			}
		}

//		r($namespace, $GLOBALS['B2_COMPOSER']->getPrefixesPsr4(), $view);

		return $view;

		require_once(__DIR__.'/../inc/funcs.php');
		$x = Page::factory();
//		$x->set_attr('headers', []);//['Content-Type' => 'text/plain']);
		$x->set_attr('content', '<html><head><title>Test</title></head><body>Hello world</body></html>');
		return $x;
	}

	function run()
	{
		// Пробуем искать по-новому
		// ...
		// https://github.com/phpixie/http
		// http://habrahabr.ru/post/256639/

//		PHPixie пока не трогаем, там какой-то свой PSR-7.
//		$slice = new \PHPixie\Slice();
//		$http = new \PHPixie\HTTP($slice);
//		$request = $http->request();
//		r($request->server()->get('http_host'));
//		r($request->query()->uri());
//		r((string)$request->uri());

		$view = NULL;

		// composer: zendframework/zend-diactoros
		if(class_exists('\\Zend\\Diactoros\\ServerRequestFactory'))
		{
			$request = \Zend\Diactoros\ServerRequestFactory::fromGlobals();
//			$request = \Slim\Http\Request::createFromEnvironment(new \Slim\Http\Environment($_SERVER));

//			r((string)$request->getUri());
//			r((string)$request->getMethod());

			$view = $this->route_view($request);
		}

		if($view)
		{
			$response = $view->response();
//			r($response, $view);
			if($response)
			{
				$app = new \Slim\App;
				$app->respond($response);
				return;
			}
		}

		// Если ничего не нашли, то запускаем старый движок.
		return parent::run();
	}
}
