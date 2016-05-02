<?php

namespace B2;

class Project extends Obj
{
	static $routers = [];
	var $inited = false;
	var $router = NULL;
	var $apps = [];

	/**
	 * @return bors_project
     */
	static function instance($host = '', $path = '/')
	{
		static $instance = NULL;
		if(!$instance)
		{
			$caller = get_called_class();
			$instance = new $caller(NULL);
			$instance->router = new \B2\Router($instance);
			$instance->set_base_proto($host ? 'http://' : '');
			$instance->set_base_host($host);
			$instance->set_base_path($path);
			$instance->set_base_url(($host ? 'http://' : '') . $host . $path);
		}

		return $instance;
	}

	function router() { return $this->router; }

	function _title_def()
	{
		return config('project.title');
	}

	function _nav_name_def()
	{
		return config('project.nav_name');
	}

	function _url_def()
	{
		return $this->base_url();
	}

	// Файлы проектов грузятся раньше конфигурации объектов и потому сами не конфигурируются.
	// Иначе получается бесконечная рекурсия.

	function _class_prefix_def()
	{
		return \bors_core_object_defaults::project_name($this);
	}

	function b2_configure()
	{
	}

	function object_type() { return 'project'; }

	function object_data()
	{
		return array();
	}

	function config_class()
	{
		return NULL;
	}

	function data_load()
	{
		return false;
	}

	function __call($method, $params)
	{
		// Проверяем. Если мы ещё не проинициализированы, то ошибка. Например, вызов несуществуующего метода в загрузчике.
		if(!defined('BORS_CORE'))
			throw new \Exception('Unknown method ' . $method . ' for ' . get_class() . ' in uninitialized project.');

		// Иначе обрабатываем как обычно.
		return parent::__call($method, $params);
	}

	/**
	 * @return $this
	 */
	function debug()
	{
		if(class_exists('Tracy\\Debugger'))
		{
			\Tracy\Debugger::enable(\Tracy\Debugger::DEVELOPMENT);
			\Tracy\Debugger::$strictMode = true;
		}

		$this->setCfg('mode.debug', true);

		// config_set('debug_redirect_trace', true);

		return $this;
	}

	/**
	 * @return $this
     */
	function init()
	{
		if($this->inited)
			return $this;

		if(!defined('COMPOSER_ROOT'))
			define('COMPOSER_ROOT', dirname(dirname(dirname(dirname(dirname(__DIR__))))));

		if(!defined('BORS_CORE'))
			define('BORS_CORE', COMPOSER_ROOT.'/vendor/balancer/bors-core');

		//TODO: Отказаться в будущем от использования define.
		//TODO: иначе выходит несовместимость множественности проектов.
		if(!defined('BORS_SITE'))
		{
			// Ищем каталог композера или старого формата классов уровнем
			// выше, чем каталог класса проекта

			$reflector = new \ReflectionClass($this);
			$class_file = $reflector->getFileName();

			$bors_site = dirname($class_file);

			while($bors_site && $bors_site != '/' && !file_exists("$bors_site/composer.json") && !file_exists("$bors_site/classes"))
				$bors_site = dirname($bors_site);

			if($bors_site > '/')
				define('BORS_SITE', $bors_site);
		}

		if(method_exists($this, 'project_name'))
			$project_name = $this->project_name();
		elseif(property_exists($this, 'project_name'))
			$project_name = $this->project_name;
		else
			$project_name = NULL;

		//TODO: Отказаться в будущем от использования define.
		//TODO: иначе выходит несовместимость множественности проектов.
		if(!defined('BORS_HOST')
			&& $project_name
			&& file_exists($bors_host = COMPOSER_ROOT."/$project_name")
		)
			define('BORS_HOST', $bors_host);

		if(file_exists($cfg = COMPOSER_ROOT.'/config-host.php'))
			require_once($cfg);

		if($this->router())
			$this->router()->init();

		$this->inited = true;
		return $this;
	}

	/**
	 * @param string $key
	 * @param mixed  $value
	 * @return $this
	 */
	function set_cfg($key, $value)
	{
		return $this->setCfg($key, $value);
	}

	function config_host($file)
	{
		$GLOBALS['bors']['config']['config_hosts'][] = $file;
		return $this;
	}

	/**
	 * @param string $key
	 * @param mixed  $value
	 * @return $this
	 */
	function setCfg($key, $value)
	{
		$GLOBALS['cms']['config'][$key] = $value;

		return $this;
	}

	function cfg($key, $default = NULL)
	{
		if(array_key_exists($key, $GLOBALS['cms']['config']))
			return  $GLOBALS['cms']['config'][$key];

		return $default;
	}

	function regRouter($router_class, $base_url = '', $domain = '')
	{
		$router = call_user_func(array($router_class, 'factory'));
		$router->routes_init($base_url, $domain);
		self::$routers[$domain][] = $router;
		return $this;
	}

	function route_view($request)
	{
		// (string)$request->getUri() -> full uri with scheme://host/path/...
		// $request->getUri()->getPath() = string
		$path = $request->getUri()->getPath();

		$camel_path = join("\\", array_map('ucfirst', explode('/', rtrim($path, '/'))));

		$camel_path = join('', array_map('ucfirst', explode('-', $camel_path)));

		$reflection = new \ReflectionClass($this);
		$namespace = $reflection->getNamespaceName();
		$base_class =  $namespace . $camel_path;

		if(class_exists($base_class))
		{
//			$view = $base_class::load(NULL);
			$view = new $base_class(NULL); // Now simple direct load.
			if($view->get('route') == 'auto')
			{
				$view->set_attr('parents', [dirname($path).'/']);
				$view->b2_configure();
				bors()->set_main_object($view);
				return $view;
			}
		}

		if(class_exists($cn = $base_class.'\\Main'))
		{
			$view = new $cn(NULL); // Now simple direct load.
			if($view->get('route') == 'auto')
			{
				$view->set_attr('parents', [dirname($path).'/']);
				$view->b2_configure();
				bors()->set_main_object($view);
				return $view;
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
					$view->b2_configure();
					bors()->set_main_object($view);
					return $view;
				}
			}
		}

		$view = NULL;

		$namespace .= "\\";

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
						$view->b2_configure();
						$view->storage()->load($view);
					}
				}

				foreach(glob($test_path.'/Main.*') as $file)
				{
					if(preg_match('!^.+/(\w+\.md.tpl)$!', $file))
					{
						$view = new \bors_page_fs_markdown($file);
						$view->set_attr('parents', [dirname($path).'/']);
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

	function register_app($app_class, $base_url)
	{
		$this->apps[] = $app_class::instance($base_url);
		return $this;
	}

	/**
	 * Main routing process run
     */

	function run()
	{
		if(!$this->inited)
			$this->init();

		if(!defined('BORS_SITE'))
			define('BORS_SITE', COMPOSER_ROOT);


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
			$ret = $view->pre_show();
			if($ret)
				return $ret;

			$response = $view->response();
//			r($response, $view);
			if($response)
			{
				// composer: slim/slim>3
				$app = new \Slim\App;
				$app->respond($response);
				return;
			}
		}

		// Если ничего не нашли, то запускаем старый движок.
		return \bors::run();
	}
}
