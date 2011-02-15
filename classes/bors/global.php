<?php

// Глобальный класс для общих данных

class bors_global extends base_empty
{
	var $__user = false; // Именно false, т.к. NULL - вполне допустимое значение.
	var $__main_object = NULL;

	function user()
	{
		if($this->__user === false)
		{
			$uc = config('user_class');
			if(!$uc)
			{
				debug_hidden_log('__critical', 'Not defined user_class', true, array('dont_show_user' => true));
				return NULL;
			}

			$this->__user = object_load($uc, -1);

			if($this->__user && $this->__user->get('last_visit_time') < time()-300) // не стоит обновляться чаще раза в 5 минут
				$this->__user->set_last_visit_time(time(), true); // global $now тут не прокатит, т.к. может вызываться до инициализации конфигов.
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

	function set_main_object($obj) { return $this->__main_object = $obj; }
	function main_object() { return $this->__main_object; }

	private $changed_objects = array();

	function add_changed_object($obj) { $this->changed_objects[$obj->internal_uri()] = $obj; }

	function drop_changed_object($obj)
	{
		if(is_object($obj))
			unset($this->changed_objects[$obj->internal_uri()]);
		else
			unset($this->changed_objects[$obj]);
	}

	function have_changed_objects() { return !empty($this->changed_objects); }
	function changed_objects() { return $this->changed_objects; }

	function drop_all_caches() { bors_object_caches_drop(); }
	function memory_usage() { return round(memory_get_usage()/1048576)."/".round(memory_get_peak_usage()/1048576)."MB"; }

	function changed_save()
	{
		include_once('engines/search.php');

		if(empty($this->changed_objects))
			return;

		foreach($this->changed_objects as $name => $x)
		{
			$obj = $x;
			if(!$obj->id() || empty($obj->changed_fields))
				continue;
//var_dump($obj->changed_fields);
//var_dump($obj->data);
//			$obj->cache_clean();
			if($obj != $x)
			{
				debug_hidden_log('__workaround', "strange object cache clean error: {$x} -> {$obj}");
				$obj = $x;
			}

			if(($storage_class = $obj->storage_engine()))
			{
///			if(!$storage_class)
//				$storage_class = 'storage_db_mysql_smart';
//				debug_exit('Not defined storage engine for '.$obj->class_name());

				$storage = object_load($storage_class);

				//TODO: уже можно снести проверку в следующей строке?
				if(!(method_exists($obj, 'skip_save') && $obj->skip_save())) //TODO: костыль для bors_admin_image_append
				{
					$storage->save($obj);
					if(config('debug_trace_changed_save'))
						echo 'Save '.$obj->debug_title()."\n";
				}
			}

			save_cached_object($obj);
			$this->drop_changed_object($obj);
			$obj->cache_clean();

			if(config('search_autoindex') && $obj->auto_search_index())
			{
				if(config('bors_tasks'))
					bors_tools_tasks::add_task($obj, 'bors_task_index', 0, -10);
				else
					bors_search_object_index($obj, 'replace');
			}
		}

		$this->changed_objects = false;
	}

	function real_uri($uri)
	{
		if(!preg_match("!^([\w/]+)://(.*[^/])(/?)$!", $uri, $m))
			return "";
		if($m[1] == 'http')
			return $uri;

		$cls = class_load($m[1], $m[2].(preg_match("!^\d+$!", $m[2]) ? '' : '/'));

		if(method_exists($cls, 'url'))
			return $cls->url();
		else
			return $uri;
	}

	function referer() { return empty($_GET['ref']) ? @$_SERVER['HTTP_REFERER'] : $_GET['ref']; }

	function client() { return $this->__havec('client') ? $this->__lastc() : $this->__setc(object_load('bors_client')); }
	function server() { return $this->__havec('server') ? $this->__lastc() : $this->__setc(object_load('bors_server')); }
	function request(){ return $this->__havec('request')? $this->__lastc() : $this->__setc(object_load('bors_request')); }

	function do_task($task_class, $data = array())
	{
		$client= new GearmanClient();
		$client->addServer();

		$data['worker_class_name'] = $task_class;

		$client->doBackground('balabot.work', serialize($data));
		debug_hidden_log('balabot_work', "$task_class: ".substr(serialize($data), 0, 50), false);
	}

	static function ping($loops, $message, $rest = 0)
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
					$rest_msg = 'ETA '.smart_interval(intval($rest_us+0.5));
				}

				$prev = $rest;
				$prev_ts = microtime(true);
			}

			$count = 0;
			bors()->changed_save();
			bors_object_caches_drop();
			echo '['.date('Y-m-d H:i:s').'] '.($message ? "{$message} " : '').self::memory_usage()." $rest_msg\n";
		}

//		var_dump($GLOBALS);
	}
}
