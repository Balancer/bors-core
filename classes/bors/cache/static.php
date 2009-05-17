<?php

class cache_static extends base_object_db
{
	function main_db_storage() { return config('cache_database'); }
	function main_table() { return 'cached_files'; }
	function main_table_fields()
	{
		return array(
			'id' => 'file',
			'object_uri' => 'uri',
			'original_uri',
			'last_compile',
			'expire_time',
			'class_name_db' => 'class_name',
			'class_id_db' => 'class_id',
			'object_id_db' => 'object_id',
			'recreate',
		);
	}

	function object() { return object_load($this->class_id(), $this->object_id()); }

	static function drop($object)
	{
		if(!$object || !config('cache_database'))
			return;

		$caches = objects_array('cache_static', array('class_id=' => $object->class_id(), 'object_id=' => $object->id()));

		if(file_exists($object->static_file()))
			if($cache = object_load('cache_static', $object->static_file()))
				$caches[] = $cache;

		foreach($caches as $cache)
		{
			@unlink($cache->id());
			if(!file_exists($cache->id()))
				$cache->delete(false);
		}

		if($object->cache_static_recreate())
		{
			if(config('bors_tasks'))
				bors_tools_tasks::add_task($object, 'bors_task_statCacheRecreate', 0, 127);
			else
				bors_object_create($object);
		}
		else
			@unlink($object->static_file());
	}

	static function save($object, $content, $expire_time = false)
	{
		$object_id = $object->id();
//		if($object_id && !is_numeric($object_id))
//			return;
	
		$file = $object->static_file();
		if(!$file) // TODO: отловить
			return;
		
		//TODO: отловить кеш-запись постов при добавлении нового сообщения. (class_id = 1)
		
		bors()->changed_save();
		
		$cache = new cache_static($file);
		
		$cache->set_object_uri($object->url($object->page()), true);
		$cache->set_original_uri($object->called_url(), true);
		$cache->set_class_id_db($object->class_id(), true);
		$cache->set_class_name_db($object->class_name(), true);

		$cache->set_object_id_db($object->id(), true);
		$cache->set_last_compile(time(), true);
		$cache->set_expire_time(time() + ($expire_time === false ? $object->cache_static() : $expire_time), true);
		$cache->set_recreate($object->cache_static_recreate(), true);

		$cache->new_instance();
		storage_db_mysql_smart::save($cache);

		foreach(explode(' ', $object->cache_groups()) as $group_name)
			if($group_name)
				cache_group::register($group_name, $object);

		$object->set_was_cleaned(false, false);

		mkpath(dirname($file), 0777);
		@chmod(dirname($file), 0777);
		@file_put_contents($file, $content);
		@chmod($file, 0664);
		if(!file_exists($file))
			debug_hidden_log('filesystem', "Can't create static file for {$object}: {$file}");
	}
	
	function replace_on_new_instance() { return true; }
}
