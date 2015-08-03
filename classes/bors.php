<?php

class bors
{
	static function init()
	{
		if(!defined('BORS_CORE'))
			define('BORS_CORE', dirname(__DIR__));

		require_once(BORS_CORE.'/init.php');
	}

	static function log()
	{
		return bors_log_monolog::instance();
	}

	function route_view($url = NULL, $host = NULL, $port = NULL)
	{
		if(!$url)
			$url = $_SERVER['REQUEST_URI'];

		$view = bors_load_uri($url);
		return $view;
	}

	static function run()
	{
		self::init();
		require_once(BORS_CORE.'/main.php');
	}

	static function show_uri($uri)
	{
		$res = false;

		if(config('debug.execute_trace'))
			debug_execute_trace("bors_load_uri('$uri');");

		config_set('__main_object_load', true); // костыли, ну и фиг с ними. Боком нигде не должно вылезти.
		if($object = bors_load_uri($uri))
		{
			config_set('__main_object_load', false);
			config_set('__main_object', $object);

			// Если это редирект
			if(!is_object($object))
			{
				if(config('bors.version_show'))
					header('X-bors-object: redirect to '.$object);

				return go($object);
			}

			if(config('bors.version_show'))
				header('X-bors-object: '.$object->internal_uri());

			// Новый метод вывода, полностью на самом объекте
			if(method_exists($object, 'show'))
			{
				if(config('debug.execute_trace'))
					debug_execute_trace("{$object}->show()");

				$res = $object->show();
			}

			if(!$res)	// Если новый метод не обработан, то выводим как раньше.
			{
				if(config('debug.execute_trace'))
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
			$trace = debug_trace(0, false, -1, $e->getTrace());
			$message = $e->getMessage();
			debug_hidden_log('exception', "$message\n\n$trace", true, array('dont_show_user' => true));
			try
			{
				bors_message(ec("При попытке просмотра этой страницы возникла ошибка:\n")
					."<div class=\"red_box alert alert-danger\">$message</div>\n"
					.ec("Администраторы будут извещены об этой проблеме и постараются её устранить. Извините за неудобство.\n<span style=\"color: #ccc\">~~~1</span>")
					.(config('site.is_dev') ? "<pre>$trace</pre>" : "<!--\n\n$trace\n\n-->"), array(
//					'template' => 'xfile:default/popup.html',
				));
			}
			catch(Exception $e2)
			{
				bors()->set_main_object(NULL);
				bors_message(ec("При попытке просмотра этой страницы возникли ошибки:\n")
					."<div class=\"red_box\">$message</div>\n"
					.ec("Администраторы будут извещены об этой проблеме и постараются её устранить. Извините за неудобство.\n~~~2")
					.(config('site.is_dev') ? "<pre>$trace</pre>" : "<!--\n\n$trace\n\n-->"), array(
					'template' => 'xfile:default/popup.html',
				));
			}

			$res = true;
		}

		return $res;
	}

	static function find_webroot($relative_path)
	{
		if(file_exists($f = BORS_SITE.'/webroot'.$relative_path))
			return $f;

		foreach(bors_dirs() as $dir)
			if(file_exists($f = $dir.'/webroot'.$relative_path))
				return $f;

		return NULL;
	}
}
