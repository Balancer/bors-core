<?php

class cache_static extends bors_object_db
{
	function db_name() { return config('cache_database'); }
	function table_name() { return 'cached_files'; }
	function storage_engine() { return 'bors_storage_mysql'; }
	function ignore_on_new_instance() { return true; }

	function table_fields()
	{
		return array(
			'id' => 'file',
			'original_uri',
			'last_compile',
			'expire_time',
			'target_class_name' => 'class_name',
			'target_class_id' => 'class_id',
			'target_id' => 'object_id',
			'target_page',
			'recreate',
//			'bors_site',
		);
	}

	function can_have_cross() { return false; }

	function target()
	{
		if($this->__havefc())
			return $this->__lastc();

		$x = NULL;
		if($this->original_uri())
			$x = bors_load_uri($this->original_uri());

		if(!$x)
		{
			if($x = bors_load($this->target_class_name(), $this->target_id()))
				$x->set_page($this->target_page());
			else
				echo "Can't load {$this->original_uri()} nor {$this->target_class_name()}({$this->target_id()})\n";
		}

		return $x;
	}

	// Если page is null, то чистятся все файлы объекта, со всеми страницами
	// Если стоит признак recreate и объект одностраничный, то самый последний
	// файл не удаляется, а пересоздаётся поверх.
	// Поведение связки recreate + page is null для многостраничных объектов
	// не определено
	static function drop($object, $page = NULL)
	{
		if(!$object || !config('cache_database'))
			return;

		if(!preg_match('/(post.php|edit.php)/', @$_SERVER['REQUEST_URI']))
			bors_debug::syslog('000-drop', $object->debug_title());

		if($page)
		{
			$object->set_page($page);
			$caches = bors_find_all('cache_static', array(
				'target_class_id' => $object->class_id(),
				'target_id' => $object->id(),
				'target_page' => $page,
				'order' => '-last_compile',
				'by_id' => true,
			));
		}
		else
			$caches = bors_find_all('cache_static', array(
				'target_class_id' => $object->class_id(),
				'target_id' => $object->id(),
				'order' => '-last_compile',
				'by_id' => true,
			));

		$first = true; // Мы сохраняем первый загруженный объект, чтобы потом не перезагружать его снова.
		$cache = NULL;
		$need_recreate = ($cache && $cache->recreate()) || $object->cache_static_recreate();

		foreach($caches as $cache)
		{
			// Если не требуется пересоздавать, то удаляем все файлы. Иначе — все, кроме самого свежего (первого в списке)
			if(!$need_recreate || !$first)
				@unlink($cache->id());

			// Удаляем все пустые каталоги вверх по дереву.
			$d = dirname($cache->id());
			while($d && $d != '/')
			{
				@rmdir($d);
				$d = dirname($d);
			}

			// Удаляем все кеш-записи (хотя, по идее, она будет только одна), кроме первой
			// Удаляем только если файл был стёрт.
			if(!file_exists($cache->id()) && !$first)
			{
//TODO: WTF? Сообщение сверху на http://balancer.ru/society/2011/08/t82829--vybory-2011-2012.html
//http://balancer.ru/g/p2702194
//				echo "<b>delete</b>($cache), class_name={$object->class_name()}<br/>";
				$cache->delete(false);
			}

			$first = false;
		}

		// Найденную запись, если была, сохраняем для дальнейшей работы
		$object->set_attr('static_recreate_object', $cache);
		$object->set_attr('static_recreate_object', $cache);

		if($object->cache_static_recreate())
		{
			if(config('bors_tasks'))
				bors_tools_tasks::add_task($object, 'bors_task_statCacheRecreate', 0, 127); // 0 == немедленно, 127 == приоритет низкий
			else
				bors_object_create($object, $page);
		}
		elseif(preg_match('!/cache-static/!', $object->static_file()))
			@unlink($object->static_file());
		elseif($object->static_file())
			bors_debug::syslog('error-fatal-static', "Non cache-static file for ".$object->debug_title().": ".$object->static_file());
	}

	//TODO: можно делать static, если static будет у родителя. Или переименовать.
	static function save_object($object, $content, $ttl = false)
	{
		$object_id = $object->id();

		$file = $object->static_file();
		if(!$file) // TODO: отловить
		{
			bors_debug::syslog('static-file-notice', "empty static_file() for ".$object->debug_title());
			return;
		}

		if(preg_match('/,new/', $file))
			bors_debug::syslog('static-file-logic-error', "New page for ".$object->debug_title());

		//TODO: отловить кеш-запись постов при добавлении нового сообщения. (class_id = 1)
		bors()->changed_save();

		// Если это просто обновление кеша, то у нас тут будет найденная запись из ::drop
		if(config('cache_database'))
		{
			if(!($cache = $object->attr('static_recreate_object')))
				$cache = bors_load_ex('cache_static', $file, array('no_load_cache' => true));
		}
		else
			$cache = NULL;

		$object_uri = $object->url_ex($object->page());
		$original_uri = $object->called_url();

		if(!$original_uri)
			$original_uri = $object_uri;

//		if($object->class_name() == 'balancer_board_topic' || $object->class_name() == 'forum_topic')
//			bors_debug::syslog('__cache_file_register', "file=".$file."\nobject=".$object->debug_title()."\npage=".$object->page()."\ncache=".($cache?'yes':'no'));

		$expire_time = time() + ($ttl === false ? $object->cache_static() : $ttl);
//		bors_debug::syslog('00time', "o={$object->debug_title()}; ttl=$ttl, cs=".$object->cache_static()."; mt=".date("r", $object->modify_time())."; expired=".date('r', $expire_time));

		if(($ic=config('internal_charset')) != ($oc=config('output_charset')))
			$content = iconv($ic, $oc.'//translit', $content);

		bors_function_include('fs/file_put_contents_lock');

		mkpath($dir = dirname($file), 0777);

		if(is_writable($dir))
		{
			file_put_contents_lock($file, $content);
			if(is_file($file))
			{
				// Скрываем, т.к. файл может не принадлежать нашему пользователю.
				@chmod($file, 0666);

				if(!($mt = $object->modify_time()))
					$mt = time();

				@touch($file, $mt, $expire_time);
			}
		}

		if(!file_exists($file) || !is_file($file))
			bors_debug::syslog('warning-filesystem', "Can't create static file.\n
	object: {$object}\n
	file: {$file} (fe=".file_exists($file)
		.';isf='.is_file($file)
		.';isw='.is_writable($dir).")");

		if(config('cache_database'))
		{
			if($cache)
			{
//				echo "Update $file<br/>\n";
				$cache->set_target_class_name($object->class_name(), true);
				$cache->set_target_class_id($object->class_id(), true);
				$cache->set_target_id($object->id(), true);
				$cache->set_target_page($object->page(), true);
				$cache->set_last_compile(time(), true);
				$cache->set_expire_time($expire_time, true);
				$cache->set_recreate($object->cache_static_recreate(), true);

				if($original_uri)
					$cache->set('original_uri', $original_uri, true);

				$cache->store();
			}
			else
			{
				$cache = bors_new('cache_static', array(
					'id' => $file,
					'original_uri' => $original_uri,
					'target_class_name' => $object->class_name(),
					'target_class_id' => $object->class_id(),
					'target_id' => $object->id(),
					'target_page' => $object->page(),
					'last_compile' => time(),
					'expire_time' => $expire_time,
					'recreate' => $object->cache_static_recreate(),
//					'bors_site' => BORS_SITE,
				));

//				config_set('debug_mysql_queries_log', false);

//				if($object->class_name() == 'balancer_board_topic' || $object->class_name() == 'forum_topic')
//					bors_debug::syslog('__cache_file_register2', "file=".$file."\nobject=".$object->debug_title()."\npage=".$object->page()."\ncache_id=".$cache->id()."\ncache_page=".$cache->target_page());
			}

			foreach(explode(' ', $object->cache_depends_on()) as $group_name)
				if($group_name)
					cache_group::register($group_name, $object);

			foreach($object->cache_parents() as $parent_object)
				cache_group::register($parent_object->internal_uri_ascii(), $object);

//			$object->set_was_cleaned(false);
		}

	}
}
