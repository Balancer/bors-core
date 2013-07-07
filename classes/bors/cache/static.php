<?php

class cache_static extends bors_object_db
{
	function db_name() { return config('cache_database'); }
	function table_name() { return 'cached_files'; }
	function storage_engine() { return 'bors_storage_mysql'; }
	function table_fields()
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
			'target_class_id' => $object->class_id(),
			'target_id' => $object->id(),
			'by_id' => true,
		));

		$first = true; // Мы сохраняем первый загруженный объект, чтобы потом не перезагружать его снова.
		$cache = NULL;
		foreach($caches as $cache)
		{
//			echo "unlink {$cache->id()}<br/>";
//			debug_hidden_log('static-clean-unlink-5', "{$cache->id()}");
			@unlink($cache->id());
			$d = dirname($cache->id());
			while($d && $d != '/')
			{
				@rmdir($d);
				$d = dirname($d);
			}

			// Удаляем все кеш-записи (хотя, по идее, она будет только одна), кроме первой
			if(!file_exists($cache->id()) && !$first)
			{
//TODO: WTF? Сообщение сверху на http://balancer.ru/society/2011/08/t82829--vybory-2011-2012.html
//http://balancer.ru/g/p2702194
//				echo "<b>delete</b>($cache), class_name={$object->class_name()}<br/>";
				$cache->delete(false);
			}
			else
				$first = false;
		}

		// Найденную запись, если была, сохраняем для дальнейшей работы
		$object->set_attr('static_recreate_object', $cache);

		if($object->cache_static_recreate())
		{
			if(config('bors_tasks'))
				bors_tools_tasks::add_task($object, 'bors_task_statCacheRecreate', 0, 127);
			else
				bors_object_create($object);
		}
	}

	//TODO: можно делать static, если static будет у родителя. Или переименовать.
	function save($object, $content, $expire_time = false)
	{
//		echo "Save $object as {$object->static_file()}<br/>";
		$object_id = $object->id();

		$file = $object->static_file();
//		echo "Save $file\n";
		if(!$file) // TODO: отловить
			return;

		//TODO: отловить кеш-запись постов при добавлении нового сообщения. (class_id = 1)
		bors()->changed_save();

		// Если это просто обновление кеша, то у нас тут будет найденная запись из ::drop
		if(!($cache = $object->attr('static_recreate_object')))
			$cache = bors_load_ex(__CLASS__, $file, array('no_load_cache' => true));

		$object_uri = $object->url_ex($object->page());
		$original_uri = $object->called_url();

		if($cache)
		{
//			echo "Update $file<br/>\n";
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
//			echo "New $file<br/>\n";
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

		foreach($object->cache_parents() as $parent_object)
			cache_group::register($parent_object->internal_uri_ascii(), $object);

//		$object->set_was_cleaned(false);

		if(($ic=config('internal_charset')) != ($oc=config('output_charset')))
			$content = iconv($ic, $oc.'//translit', $content);

		mkpath($dir = dirname($file), 0777);

		if(is_writable($dir)) //TODO: проверить скорость. Быстрее проверка или маскировка собакой
		{
			file_put_contents($file, $content);
			@chmod($file, 0666);
		}

		if(!file_exists($file))
			debug_hidden_log('filesystem', "Can't create static file for {$object}: {$file}");
	}

	function replace_on_new_instance() { return true; }
}
