<?php

// Глобальный класс для общих данных

class bors_global extends base_empty
{
	private $user = false;
	private $main_object = NULL;
	
	function user()
	{
		if($this->user === false)
		{
			$this->user = object_load(config('user_class'), -1);

			if($this->user)
				$this->user->set_last_visit_time(time(), true); // global $now тут не прокатит, т.к. может вызываться до инициализации конфигов.
		}
		
		return $this->user;
	}

	function user_id()
	{
		return ($user = $this->user()) ? $user->id() : 0;
	}

	function set_main_object(&$obj) { return $this->main_object = &$obj; }
	function &main_object() { return $this->main_object; }

	private $changed_objects = array();
		
	function add_changed_object($obj) { $this->changed_objects[$obj->internal_uri()] = $obj; }
	function drop_changed_object($obj) { if(is_object($obj)) unset($this->changed_objects[$obj->internal_uri()]); else unset($this->changed_objects[$obj]); }
		
	function changed_save()
	{
		include_once('engines/search.php');
		
		if(empty($this->changed_objects))
			return;
				
		foreach($this->changed_objects as $name => $obj)
		{
			if(!$obj->id() || empty($obj->changed_fields))
				continue;
				
			$obj->cache_clean();
			
			if(!($storage = $obj->storage_engine()))
				$storage = 'storage_db_mysql_smart';
//				debug_exit('Not defined storage engine for '.$obj->class_name());
			
			$storage = object_load($storage);
				
			$storage->save($obj);
			save_cached_object($obj);
			$this->drop_changed_object($obj);

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
}
