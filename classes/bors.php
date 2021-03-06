<?php

use B2\Cfg;

if(empty($GLOBALS['stat']['start_microtime']))
	$GLOBALS['stat']['start_microtime'] = microtime(true);

require_once __DIR__.'/../inc/funcs.php';

class bors
{
	static $composer_class_dirs = [];
	static $composer_route_maps = [];
	static $composer_template_dirs = [];
	static $composer_smarty_plugin_dirs = [];
	static $composer_webroots = [];
	static $composer_autoroute_prefixes = [];
	static $package_apps = [];
	static $package_path = [];
	static $package_names = [];
	static $package_route_maps = [];
	static $package_app_path = [];
	static $app_routers = [];

	static function init()
	{
		if(!defined('BORS_CORE'))
			define('BORS_CORE', dirname(__DIR__));

		if(!defined('COMPOSER_ROOT'))
			define('COMPOSER_ROOT', dirname(dirname(dirname(dirname(__DIR__)))));

		// Грузим вначале, т.к. там прописаны рабочие каталоги и т.п.
		if(file_exists($f = COMPOSER_ROOT.'/bors/autoload.php'))
			require_once $f;

		require_once(__DIR__.'/../init.php');
	}

	static function init_new()
	{
		if(!empty($GLOBALS['b2_data']['inited_new']))
			return;

		$GLOBALS['b2_data']['inited_new'] = true;

		bors_transitional::init();

		if(!defined('BORS_CORE'))
			define('BORS_CORE', dirname(__DIR__));

		if(!defined('COMPOSER_ROOT'))
			define('COMPOSER_ROOT', dirname(dirname(dirname(dirname(__DIR__)))));

		if(!defined('BORS_HOST'))
			define('BORS_HOST', COMPOSER_ROOT);

		if(!ini_get('default_charset'))
			ini_set('default_charset', 'UTF-8');

		$GLOBALS['now'] = time();

		if(!Cfg::get('cache_dir'))
			Cfg::set('cache_dir', sys_get_temp_dir().DIRECTORY_SEPARATOR.'bors-cache'.DIRECTORY_SEPARATOR.join('-', bors::cache_namespace()));

		// Грузим вначале, т.к. там прописаны рабочие каталоги и т.п.
		if(file_exists($f = COMPOSER_ROOT.'/bors/autoload.php'))
			require_once $f;

		foreach(bors::$package_route_maps as $map_file)
		{
			$map = NULL;
			require_once($map_file);
			if($map)
				bors_url_map($map);
		}

		foreach(bors::$composer_route_maps as $map_file)
		{
			$map = NULL;
			require_once($map_file);
			if($map)
				bors_url_map($map);
		}
	}

	static function load($class_name, $object_id)
	{
		require_once BORS_CORE.'/engines/bors.php';
		return bors_load($class_name, $object_id);
	}

	static function log()
	{
		return bors_log_monolog::instance();
	}

	function route_view($url = NULL, $host = NULL, $port = NULL)
	{
		require_once(BORS_CORE.'/engines/bors.php');

		if(!$url)
			$url = $_SERVER['REQUEST_URI'];

		$view = bors_load_uri($url);
		return $view;
	}

	static function run()
	{
		self::init();
		return require_once __DIR__.'/../main.php';
	}

	/**
	 * @param string $uri
	 * @param string $method
	 * @return bool|string
	 * @throws Exception
	 */
	static function show_uri($uri, $method = 'GET')
	{
//		unset($GLOBALS['cms']['templates']);

		$res = false;

		if(Cfg::get('debug.execute_trace'))
			debug_execute_trace("bors_load_uri('$uri');");

		Cfg::set('__main_object_load', true); // костыли, ну и фиг с ними. Боком нигде не должно вылезти.

		$uri_info = parse_url($uri);

		$object = NULL;

		if(array_key_exists('nc', $_GET))
		{
			@unlink($_SERVER['DOCUMENT_ROOT'].$uri_info['path']);
			@unlink($_SERVER['DOCUMENT_ROOT'].'/cache-static'.$uri_info['path']);
		}

/*
		foreach(\B2\Project::$routers as $domain => $routers)
		{
			foreach($routers as $router)
			{
				$object = $router->dispatch($uri_info['path'], $method);
				if($object)
					break;
			}
		}
*/

		if(!$object)
			$object = bors_load_uri($uri);

		if($object)
		{
			Cfg::set('__main_object_load', false);
			Cfg::set('__main_object', $object);

			// Если это редирект
			if(!is_object($object))
			{
				if(Cfg::get('bors.version_show'))
					header('X-bors-object: redirect to '.$object);

				return go($object);
			}

			if(class_exists(\Tracy\Debugger::class))
			{
				\Tracy\Debugger::barDump(['class_file' => $object->class_file()], $object->debug_title());
//				\Tracy\Debugger::fireLog($object->class_file());
			}

			if(Cfg::get('bors.version_show'))
				@header('X-bors-object: '.$object->internal_uri());

			// Новый метод вывода, полностью на самом объекте
			if(method_exists($object, 'show'))
			{
				if(Cfg::get('debug.execute_trace'))
					debug_execute_trace("{$object}->show()");

				$res = $object->show();
			}

			if(!$res)	// Если новый метод не обработан, то выводим как раньше.
			{
				if(Cfg::get('debug.execute_trace'))
					debug_execute_trace("bors_object_show($object)");

				$res = bors_object_show($object);
			}
		}

		return $res;
	}

	static function try_show_uri($uri)
	{
		try
		{
			$res = self::show_uri($uri);
		}
		catch(Exception $e)
		{
			@header('HTTP/1.1 500 Internal Server Error');
			bors_function_include('debug/trace');
			bors_function_include('debug/hidden_log');
//			var_dump($e->getTrace());
			$trace = bors_debug::trace(0, false, -1, $e->getTrace());
			$message = $e->getMessage();
			bors_debug::syslog('exception', "$message\n\n$trace", true, array('dont_show_user' => true));
			try
			{
				bors_message(ec("При попытке просмотра этой страницы возникла ошибка:\n")
					."<div class=\"red_box alert alert-danger\">$message</div>\n"
					.ec("Администраторы будут извещены об этой проблеме и постараются её устранить. Извините за неудобство.\n<span style=\"color: #ccc\">~~~1</span>")
					.(Cfg::get('site.is_dev') ? "<pre>$trace</pre>" : "<!--\n\n$trace\n\n-->"), array(
//					'template' => 'xfile:default/popup.html',
				));
			}
			catch(Exception $e2)
			{
				bors()->set_main_object(NULL);
				bors_message(ec("При попытке просмотра этой страницы возникли ошибки:\n")
					."<div class=\"red_box\">$message</div>\n"
					.ec("Администраторы будут извещены об этой проблеме и постараются её устранить. Извините за неудобство.\n~~~2")
					.(Cfg::get('site.is_dev') ? "<pre>$trace</pre>" : "<!--\n\n$trace\n\n-->"), array(
					'template' => 'xfile:default/popup.html',
				));
			}

			$res = true;
		}

		return $res;
	}

	static function find_webroot($relative_path)
	{
		foreach(bors::$composer_webroots as $dir)
			if(file_exists($f = $dir.$relative_path))
				return $f;

		if(file_exists($f = BORS_SITE.'/webroot'.$relative_path))
			return $f;

		foreach(bors_dirs() as $dir)
			if(file_exists($f = $dir.'/webroot'.$relative_path))
				return $f;

		return NULL;
	}

	// BORS process namespace
	static function cache_namespace($user_perms = true)
	{
		$ns_parts = array();
		if(empty($_SERVER['HTTP_HOST']))
			$ns_parts[] = 'cli';
		elseif(!preg_match('/^\d+\.\d+\.\d+\.\d+$/', $_SERVER['HTTP_HOST'])) // Если это доменное имя
			$ns_parts[] = str_replace(':', '=', strtolower($_SERVER['HTTP_HOST']));
		elseif($_SERVER['HTTP_HOST'] != gethostbyname(gethostname()))
			$ns_parts[] = 'unknown';

		$ns_parts[] = Cfg::get('project.name');

		if($user_perms && !empty($_SERVER['USER']))
			$ns_parts[] = strtolower($_SERVER['USER']);

		if(($cs = Cfg::get('internal_charset')) && $cs != 'utf-8')
			$ns_parts[] = 'i'.$cs;

		if(($cs = Cfg::get('output_charset')) && $cs != 'utf-8')
			$ns_parts[] = 'o'.$cs;

		if(!empty($_SERVER['BORS_INSTANCE']))
			$ns_parts[] = $_SERVER['BORS_INSTANCE'];

		return array_filter($ns_parts);
	}
}
