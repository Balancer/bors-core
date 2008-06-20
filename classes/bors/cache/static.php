<?php

class cache_static extends base_object_db
{
	function main_db_storage() { return config('cache_database'); }
	function main_table_storage() { return 'cached_files'; }
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
		);
	}

	function object() { return object_load($this->class_id(), $this->object_id()); }

	static function drop($object)
	{
		if(!$object)
			return;
	
		$caches = objects_array('cache_static', array('class_id=' => $object->class_id(), 'object_id=' => $object->id()));
		$cache = object_load('cache_static', $object->static_file());
		if($cache)
			$caches[] = $cache;
	
		foreach($caches as $cache)
		{
			@unlink($cache->id());
			if(!file_exists($cache->id()))
				$cache->delete();
		}
	}
	
	static function save($object, $content)
	{
		$object_id = $object->id();
		if(!$object_id && !is_numeric($object_id))
			return;
	
		$file = $object->static_file();
		if(!$file) // TODO: отловить
			return;
		
		//TODO: отловить кеш-запись постов при добавлении нового сообщения. (class_id = 1)
		
		bors()->changed_save();
		
		$cache = new cache_static($file);
		$cache->new_instance();
		
		$cache->set_object_uri($object->url($object->page()), true);
		$cache->set_original_uri($object->called_url(), true);
		$cache->set_class_id_db($object->class_id(), true);
		$cache->set_class_name_db($object->class_name(), true);

		$cache->set_object_id_db($object->id(), true);
		$cache->set_last_compile(time(), true);
		$cache->set_expire_time($object->cache_static()+time(), true);

		storage_db_mysql_smart::save($cache);

		@mkdir(dirname($file), 0777, true);
		@file_put_contents($file, $content);
	}
}
