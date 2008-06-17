<?php

class cache_static extends base_object_db
{
	function main_db_storage() { return 'CACHE'; }
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

	function drop()
	{
		@unlink($this->id());
		if(file_exists($this->id()))
			return;
		
		$this->delete();
	}
	
	static function save($object, $content)
	{
		$file = $object->static_file();
		$cache = object_load('cache_static', $file);
		if(!$cache)
			$cache = object_new_instance('cache_static', $file);
		
		$cache->set_object_uri($object->url(), true);
		$cache->set_original_uri($object->called_url(), true);
		$cache->set_class_id_db($object->class_id(), true);
		$cache->set_class_name_db($object->class_name(), true);

		$cache->set_object_id_db($object->id(), true);
		$cache->set_last_compile(time(), true);
		$cache->set_expire_time($object->cache_static()+time(), true);

		bors()->changed_save();

		file_put_contents($file, $content);
	}
}
