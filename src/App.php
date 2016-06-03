<?php

namespace B2;

class App extends Obj
{
	var $routers = [];
	var $is_initialized = false;
	var $router = NULL;
	var $apps = [];

	/**
	 * @param string $host
	 * @param string $path
	 * @return App
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

		if(empty($GLOBALS['B2']['main_app']))
			$GLOBALS['B2']['main_app'] = $instance;

		return $instance;
	}

	/**
	 * @return \B2\Router
     */
	function router()
	{
		return $this->router;
	}

	function router_instance($router_class)
	{
		if(empty($this->routers[$router_class]))
		{
			$router = call_user_func([$router_class, 'instance'], $this);
			$this->routers[$router_class] = $router;
			$router->init();
		}

		return $this->routers[$router_class];
	}

	function _title_def()
	{
		return config('project.title');
	}

	function _nav_name_def()
	{
		return config('project.nav_name');
	}

	/**
	 * @return string
     */
	function _url_def()
	{
		return $this->base_url();
	}

	/**
	 * @param $foo
	 * @return string
     */
	function _url_ex_def($foo)
	{
		return $this->url();
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
		config_set('debug.timing', true);

		return $this;
	}

	/**
	 * @return $this
     */
	function init()
	{
		if($this->is_initialized)
			return $this;

		if(!defined('COMPOSER_ROOT'))
			define('COMPOSER_ROOT', dirname(dirname(dirname(dirname(dirname(__DIR__))))));

		if(!defined('BORS_CORE'))
			define('BORS_CORE', COMPOSER_ROOT.'/vendor/balancer/bors-core');

		require_once BORS_CORE.'/config.php';

		if(file_exists(COMPOSER_ROOT.'/config.php'))
			require_once COMPOSER_ROOT.'/config.php';

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
			/** @var string $project_name */
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

		if(file_exists(COMPOSER_ROOT.'/config-host.php'))
			require_once COMPOSER_ROOT.'/config-host.php';

		if($this->router())
			$this->router()->init();

		$this->is_initialized = true;
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

	function route_view($request)
	{
		// (string)$request->getUri() -> full uri with scheme://host/path/...
		// $request->getUri()->getPath() = string
		$path = $request->getUri()->getPath();

		$router_classes = @\bors::$app_routers[$this->class_name()];
		if($router_classes)
		{
			foreach($router_classes as $rc)
			{
				$router = $this->router_instance($rc);
				if($view = $router->dispatch($request))
					return $view;
			}
		}

		$router = $this->router_instance('B2\\Router\\AutoPhp');
		if($view = $router->dispatch($request))
			return $view;

		$router = $this->router_instance('B2\\Router\\AutoMarkdown');
		if($view = $router->dispatch($request))
			return $view;

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
		if(!$this->is_initialized)
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

		if($this->cfg('view.preset'))
			$view = $this->cfg('view.preset');

		// composer: zendframework/zend-diactoros
		if(!$view && class_exists('\\Zend\\Diactoros\\ServerRequestFactory'))
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

	function bors_init()
	{
		\bors::init();
		return $this;
	}

	function package_path()
	{
		return \bors::$package_app_path[get_class($this)];
	}
}
