<?php

class cache_static extends base_object_db
{
	function main_db() { return config('cache_database'); }
	function main_table() { return 'cached_files'; }
	function main_table_fields()
	{
		return array(
			'id' => 'file',
			'object_uri' => 'uri',
			'original_uri',
			'last_compile',
			'expire_time',
			'target_class_name' => 'class_name',
			'target_class_id' => 'class_id',
			'target_id' => 'object_id',
			'recreate',
		);
	}

	function auto_targets()
	{
		return array_merge(parent::auto_targets(), array(
			'target' => 'target_class_id(target_id)',
		));
	}

	static function drop($object)
	{
		if(!$object || !config('cache_database'))
			return;

		$caches = bors_find_all('cache_static', array('target_class_id' => $object->extends_class_id(), 'target_id' => $object->id()));

		if(file_exists($object->static_file()))
			if($cache = object_load('cache_static', $object->static_file()))
				$caches[] = $cache;

		foreach($caches as $cache)
		{
			@unlink($cache->id());
			$d = dirname($cache->id());
			while($d && $d != '/')
			{
				@rmdir($d);
				$d = dirname($d);
			}

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

	//TODO: можно делать static, если static будет у родителя. Или переименовать.
	function save($object, $content, $expire_time = false)
	{
		$object_id = $object->id();

		$file = $object->static_file();

		if(!$file) // TODO: отловить
			return;

		//TODO: отловить кеш-запись постов при добавлении нового сообщения. (class_id = 1)

		bors()->changed_save();

		$cache = bors_new('cache_static', array(
			'id' => $file,
			'object_uri' => $object_uri,
			'original_uri' => $original_uri,
			'target_class_name' => $object->extends_class_name(),
			'target_class_id' => $object->extends_class_id(),
			'target_id' => $object->id(),
			'last_compile' => time(),
			'expire_time' => time() + ($expire_time === false ? $object->cache_static() : $expire_time),
			'recreate' => $object->cache_static_recreate(),
		));

		foreach(explode(' ', $object->cache_depends_on()) as $group_name)
			if($group_name)
				cache_group::register($group_name, $object);

		$object->set_was_cleaned(false, false);

		if(($ic=config('internal_charset')) != ($oc=config('output_charset')))
		{
			debug_hidden_log('iconv', "$ic -> $oc");
			$content = iconv($ic, $oc.'//translit', $content);
		}

		mkpath($dir = dirname($file), 0777);

		if(is_writable($dir)) //TODO: проверить скорость. Быстрее проверка или маскировка собакой
		{
			file_put_contents($file, $content);
			@chmod($file, 0664);
		}

		if(!file_exists($file))
			debug_hidden_log('filesystem', "Can't create static file for {$object}: {$file}");
	}

	function replace_on_new_instance() { return true; }
}
