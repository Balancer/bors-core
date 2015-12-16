<?php

// Глобальный класс для общих данных

class bors_global extends bors_object_simple
{
	var $__user = false; // Именно false, т.к. NULL - вполне допустимое значение.
	var $__main_object = NULL;

	/**
	 * @return bors_user|null
     */
	function user()
	{
		if($this->__user === false)
		{
			// Блокируем реентерабельность, иначе на ошибках легко словить бесконечный цикл.
			$this->__user = NULL;

			if(config('debug.execute_trace'))
				debug_execute_trace("bors()->user(): first load");

			$uc = config('user_class');
			if(!$uc)
			{
				if(!config('user_class_skip'))
				{
					bors_function_include('debug/hidden_log');
					debug_hidden_log('__critical', 'Not defined user_class', true, array('dont_show_user' => true));
    			}

				return NULL;
			}

			if(config('debug.execute_trace'))
				debug_execute_trace("bors()->user(): load $uc(-1)");

			$this->__user = bors_load($uc, -1);

			if($this->__user && $this->__user->get('last_visit_time') < time()-300) // не стоит обновляться чаще раза в 5 минут
				$this->__user->set_last_visit_time(time()); // global $now тут не прокатит, т.к. может вызываться до инициализации конфигов.

			if(config('debug.execute_trace'))
				debug_execute_trace("bors()->user(): done");
		}

		return $this->__user;
	}

	function user_id()
	{
		return ($user = $this->user()) ? $user->id() : NULL;
	}

	function user_title()
	{
		return ($user = $this->user()) ? $user->title() : NULL;
	}

    /**
     * @param bors_object $object
     * @return bors_object
     */
    function set_main_object($object)
	{
		if($object && $object->get('object_type') == 'project')
			return $object;

		if($this->__main_object && $object)
		{
			debug_hidden_log('__arch_error', "Set new main object '{$object->debug_title()}' with extsts '{$this->__main_object->debug_title()}'");
			if(config('is_developer'))
			{
				echo "Set main object {$object}";
				echo debug_trace();
			}
			return $this->__main_object;
		}

		return $this->__main_object = $object;
	}

    /**
     * @return bors_object
     */
    function main_object() { return $this->__main_object; }

	private $changed_objects = array();

    /**
     * @param bors_object $object
     */
    function add_changed_object($object)
	{
//		echo "Add {$obj->debug_title()}<Br/>"; echo debug_trace();
		$this->changed_objects[$object->internal_uri_ascii()] = $object;
	}

    /**
     * @param bors_object|mixed $object
     */
    function drop_changed_object($object)
	{
		if(is_object($object))
			unset($this->changed_objects[$object->internal_uri()]);
		else
			unset($this->changed_objects[$object]);
	}

	function have_changed_objects() { return !empty($this->changed_objects); }
	function changed_objects() { return $this->changed_objects; }

	function drop_all_caches() { bors_object_caches_drop(); }
	function memory_usage() { return round(memory_get_usage()/1048576)."/".round(memory_get_peak_usage()/1048576)."MB"; }

    /**
     *
     */
    function changed_save()
	{
		static $entered = false;
		if($entered)
			return;

		$entered = true;

		if(!empty($this->changed_objects))
		{
			foreach($this->changed_objects as $name => $x)
			{
                /** @var bors_object $obj */
                $obj = $x;
				if(!$obj->id() || empty($obj->changed_fields))
					continue;

				if($obj != $x)
				{
					debug_hidden_log('__workaround', "strange object cache clean error: {$x} -> {$obj}");
					$obj = $x;
				}

				$obj->store();
			}

			$this->changed_objects = false;
			$entered = false;
		}

		if(!empty($GLOBALS['bors_data']['classes_cache_updates']))
			foreach($GLOBALS['bors_data']['classes_cache_updates'] as $class_name => $x)
				bors_class_loader::classes_cache_data_save($class_name, $x['cache_data'], $x['class_file']);

		$entered = false;
	}

    /**
     * @param string $uri
     * @return string
     */
    function real_uri($uri)
	{
		if(!preg_match('!^([\w/]+)://(.*[^/])(/?)$!', $uri, $m))
			return "";

        if($m[1] == 'http')
			return $uri;

		$obj = bors_load($m[1], $m[2].(preg_match('!^\d+$!', $m[2]) ? '' : '/'));

		if(method_exists($obj, 'url'))
			return $obj->url();
		else
			return $uri;
	}

    function referer()
    {
        return empty($_GET['ref']) ? @$_SERVER['HTTP_REFERER'] : $_GET['ref'];
    }

    /**
     * @return bors_client
     */
    function client()
    {
        return $this->__havec('client') ? $this->__lastc() : $this->__setc(object_load('bors_client'));
    }

    /**
     * @return bors_server
     */
    function server()
    {
        return $this->__havec('server') ? $this->__lastc() : $this->__setc(object_load('bors_server'));
    }

    /**
     * @return bors_request
     */
    function request()
    {
        return $this->__havec('request') ? $this->__lastc() : $this->__setc(object_load('bors_request'));
    }

	function do_task($task_class, $data = array())
	{
		if(!class_exists('GearmanClient'))
		{
			debug_hidden_log('setup-error', "Can't use hardcoded GearmanClient");
			return;
		}

		$client= new GearmanClient();
		$client->addServer();

		$data['worker_class_name'] = $task_class;

		$client->doBackground('balabot.work', serialize($data));
		debug_hidden_log('balabot_work', "$task_class: ".substr(serialize($data), 0, 50), false);
	}

	static function ping($loops, $message = NULL, $rest = 0)
	{
		static $count = 0;
		static $prev  = 0;
		static $prev_ts  = 0;

		$rest_msg = NULL;

		if($count++ > $loops)
		{
			if($rest)
			{
				if($prev)
				{
					$rest_us = $rest*(microtime(true) - $prev_ts)/($prev - $rest);
					$rest_msg = 'ETA '.bors_lib_time::smart_interval(intval($rest_us+0.5));
				}

				$prev = $rest;
				$prev_ts = microtime(true);
			}

			$count = 0;
			bors()->changed_save();
			bors_object_caches_drop();
			echo '['.date('Y-m-d H:i:s').'] '.(@$message ? "{$message} " : '').self::memory_usage()." $rest_msg\n";
		}

//		var_dump($GLOBALS);
	}

	static $global_data = array();
	static function gvar($name, $def = NULL) { return array_key_exists($name, self::$global_data) ? self::$global_data[$name] : $def; }
	static function set_gvar($name, $value) { return self::$global_data[$name] = $value; }
}
