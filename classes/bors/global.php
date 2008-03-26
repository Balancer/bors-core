<?php

// Глобальный класс для общих данных

class bors_global extends base_empty
{
	private $user = NULL;
	private $main_object = NULL;
	
	function user()
	{
		if($this->user === NULL)
		{
			require_once('obsolete/users.php');
		
			if(!class_exists('User'))
				return $this->user = false;
		
			global $me;
			if(empty($me) || !is_object($me))
				$me = &new User();

			$id = $me->get('id');

			if(!$id || $id == 1)
				return $this->user = false;
//			echo "Current user id = $id<br />";

			$this->user = object_load(config('user_class'), $id);
		}
		
		return $this->user;
	}

	function set_main_object($obj) { return $this->main_object = $obj; }
	function main_object() { return $this->main_object; }

	private $changed_objects = array();
		
	function add_changed_object($obj)
	{
		$this->changed_objects[$obj->internal_uri()] = $obj;
	}
		
	function changed_save()
	{
		include_once('engines/search.php');
		
		if(empty($this->changed_objects))
			return;
				
		foreach($this->changed_objects as $name => $obj)
		{
			if(!$obj->id())
				continue;
				
			$obj->cache_clean();
			
			if(!($storage = $obj->storage_engine()))
				$storage = 'storage_db_mysql_smart';
//				debug_exit('Not defined storage engine for '.$obj->class_name());
			
			$storage = object_load($storage);
				
			$storage->save($obj);
			save_cached_object($obj);
					
			if(config('search_autoindex') && $obj->auto_search_index())
				bors_search_object_index($obj, 'replace');
		}
			
		$this->changed_objects = false;
	}
		
	function get_html($object)
	{
		require_once('funcs/templates/bors.php');
		$object->template_data_fill();
		return template_assign_bors_object($object);
	}
		
	function show($object)
	{
		echo $this->get_html($object);
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
}
