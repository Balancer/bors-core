<?php

namespace B2;

class Router extends Obj
{
	private $dispatcher;

	function routes_init($base_url, $domain = NULL)
	{
		$this->dispatcher = \FastRoute\simpleDispatcher(function(\FastRoute\RouteCollector $r) use($base_url) {
			foreach($this->routes as $path => $class_name)
			{
//				echo "addRoute('GET', '$base_url$path', $class_name);<br/>\n";
				$r->addRoute('GET', $base_url.$path, $class_name);
			}
		});
	}

	function dispatch($uri, $method = 'GET')
	{
		$info = $this->dispatcher->dispatch($method, $uri);
//		r($uri, $method, $info, $this->dispatcher);
		switch($info[0])
		{
			case \FastRoute\Dispatcher::NOT_FOUND:
				// ... 404 Not Found
				break;
			case \FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
				$allowedMethods = $info[1];
				// ... 405 Method Not Allowed
				break;
			case \FastRoute\Dispatcher::FOUND:
				$handler = $info[1];
				$vars = $info[2];
				// ... call $handler with $vars
//				r($vars);
				$object = bors_load_ex($handler, @$vars['id'], $vars);
				$object->set_parents(array(dirname(rtrim($uri, '/')).'/'));
				return $object;
				break;
		}

		return NULL;
	}

	static function factory($foo=NULL)
	{
		$class_name = get_called_class();
		$router = new $class_name(NULL);
		//TODO: тут добавить стандартную инициацию.
		return $router;;
	}
}
