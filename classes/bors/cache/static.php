<?php

class cache_static extends base_object_db
{
	function main_db() { return config('cache_database'); }
	function main_table() { return 'cached_files'; }
	function storage_engine() { return 'bors_storage_mysql'; }
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
			'bors_site',
		);
	}

	function auto_targets()
	{
		return array_merge(parent::auto_targets(), array(
			'_target' => 'target_class_id(target_id)',
		));
	}

	function target()
	{
		if($this->__havefc())
			return $this->__lastc();

		$x = NULL;
		if($this->original_uri())
			$x = bors_load_uri($this->original_uri());

		if(!$x && $this->object_uri())
			$x = bors_load_uri($this->object_uri());

		if(!$x)
		{
			echo "Can't load {$this->original_uri()} nor {$this->object_uri()}\n";
			$x = bors_load($this->target_class_name(), $this->target_id());
		}

		return $x;
	}

	static function drop($object)
	{
		if(!$object || !config('cache_database'))
			return;

		$caches = bors_find_all('cache_static', array(
			'target_class_id' => $object->extends_class_id(),
			'target_id' => $object->id(),
			'by_id' => true,
		));

		if(file_exists($object->static_file()))
			if($cache = bors_load('cache_static', $object->static_file()))
				$caches[$cache->id()] = $cache;

		$first = true;
		$cache = NULL;
		foreach($caches as $cache)
		{
			@unlink($cache->id());
			$d = dirname($cache->id());
			while($d && $d != '/')
			{
				@rmdir($d);
				$d = dirname($d);
			}

			if(!file_exists($cache->id()) && !$first)
				$cache->delete(false);
			else
				$first = false;
		}

		$object->set_attr('static_recreate_object', $cache);

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

		if(!($cache = $object->attr('static_recreate_object')))
			$cache = bors_load_ex(__CLASS__, $file, array('no_load_cache' => true));

		$object_uri = $object->url($object->page());
		$original_uri = $object->called_url();

		if($cache)
		{
//			echo "Update $file\n";
			$cache->set_target_class_name($object->extends_class_name(), true);
			$cache->set_target_class_id($object->extends_class_id(), true);
			$cache->set_target_id($object->id(), true);
			$cache->set_last_compile(time(), true);
			$cache->set_expire_time(time() + ($expire_time === false ? $object->cache_static() : $expire_time), true);
			$cache->set_recreate($object->cache_static_recreate(), true);

			if($object_uri)
				$cache->set('object_uri', $object_uri, true);

			if($original_uri)
				$cache->set('original_uri', $original_uri, true);

			$cache->store();
		}
		else
		{
//			echo "New $file\n";
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
				'bors_site' => BORS_SITE,
			));
		}

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
