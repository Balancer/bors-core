<?php

use Tracy\Debugger;

class bors_project extends bors_object
{
	static function instance()
	{
		static $instance = NULL;
		if(!$instance)
		{
			$caller = get_called_class();
			$instance = new $caller(NULL);
		}

		return $instance;
	}

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
		return '/';
	}

	// Файлы проектов грузятся раньше конфигурации объектов и потому сами не конфигурируются.
	// Иначе получается бесконечная рекурсия.

	function _class_prefix_def()
	{
		return bors_core_object_defaults::project_name($this);
	}

	function _configure()
	{
	}

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
			throw new Exception('Unknown method ' . $method . ' for ' . get_class() . ' in uninitialized project.');

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
			Debugger::enable(Debugger::DEVELOPMENT);
			Debugger::$strictMode = true;
		}

		$this->set_cfg('mode.debug', true);

		// config_set('debug_redirect_trace', true);

		return $this;
	}

	function init()
	{
		if(!defined('COMPOSER_ROOT'))
			define('COMPOSER_ROOT', dirname(dirname(dirname(dirname(dirname(__DIR__))))));

		//TODO: Отказаться в будущем от использования define.
		//TODO: иначе выходит несовместимость множественности проектов.
		if(!defined('BORS_SITE'))
		{
			// Ищем каталог композера или старого формата классов уровнем
			// выше, чем каталог класса проекта

			$reflector = new ReflectionClass($this);
			$class_file = $reflector->getFileName();

			$bors_site = dirname($class_file);

			while($bors_site && $bors_site != '/' && !file_exists("$bors_site/composer.json") && !file_exists("$bors_site/classes"))
				$bors_site = dirname($bors_site);

			if($bors_site > '/')
				define('BORS_SITE', $bors_site);
		}

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

	function run()
	{
		if(!defined('COMPOSER_ROOT'))
			define('COMPOSER_ROOT', dirname(dirname(dirname(dirname(dirname(__DIR__))))));

		if(!defined('BORS_SITE'))
			define('BORS_SITE', COMPOSER_ROOT);

		bors::run();
	}

	function setCfg($key, $value)
	{
		$GLOBALS['cms']['config'][$key] = $value;

		return $this;
	}
}
