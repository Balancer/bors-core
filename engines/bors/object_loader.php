<?php

spl_autoload_register('class_include');

bors_function_include('debug/count_inc');
bors_function_include('debug/log_var');

function bors_object_caches_drop()
{
	unset($GLOBALS['bors_data']['cached_objects4']);
//	unset($GLOBALS['bors_search_get_word_id_cache']);
}

function &load_cached_object($class_name, $id, $args, &$found=0)
{
	$obj = NULL;

	if(is_object($id) || @$args['no_load_cache'])
		return $obj;

	if(!empty($GLOBALS['bors_data']['cached_objects4'][$class_name][$id]))
	{
		$obj = &$GLOBALS['bors_data']['cached_objects4'][$class_name][$id];

		$updated = bors_class_loader_meta::cache_updated($obj);

		if($obj->can_cached() && !$updated)
		{
			$found = 1;
			return $obj;
		}
	}

	if(config('use_memcached_objects') && ($memcache = config('memcached_instance')) && call_user_func(array($class_name, 'can_cached')))
	{

		debug_count_inc('memcached checks');
		$hash = 'bors_v'.config('memcached_tag').'_'.$class_name.'://'.$id;
		if($x = unserialize($memcache->get($hash)))
		{
			$updated = bors_class_loader_meta::cache_updated($x);

			if($x->can_cached() && !$updated)
			{
				debug_count_inc('memcached loads');
				$found = 2;
				return $x;
			}
		}
	}

	$obj = NULL;
	return $obj;
}

function delete_cached_object($object) { return save_cached_object($object, true); }

function delete_cached_object_by_id($class_name, $object_id)
{
	if(($memcache = config('memcached_instance')))
	{
		$hash = 'bors_v'.config('memcached_tag').'_'.$class_name.'://'.$object_id;
		@$memcache->delete($hash);
	}

	unset($GLOBALS['bors_data']['cached_objects4'][$class_name][$object_id]);
}

function save_cached_object($object, $delete = false, $use_memcache = true)
{
	if(!method_exists($object, 'id') || is_object($object->id()))
		return;

	if($use_memcache && config('use_memcached_objects') && ($memcache = config('memcached_instance')) && $object->can_cached())
	{
		$hash = bors_objects_helper::memcache_hash_key($object);

		if($delete)
			$memcache->delete($hash); //TODO: нужен фикс вместо маскировки: http://balancer.ru/_bors/igo?o=forum_post__2171516
		else
		{
			// Маскируем @serialize() для избежание NOTICE о сериализации приватных данных
			$memcache->set($hash, @serialize($object), 0, rand(600, 1200));
			debug_count_inc('memcached stores');
			if(!array_pop($memcache->getExtendedStats()))
			{
				debug_hidden_log('__memcache_errors', "Store error for $object");
				debug_count_inc('memcached store fails');
			}
			else
				debug_count_inc('memcached store success');
		}
	}

	if($delete)
		unset($GLOBALS['bors_data']['cached_objects4'][get_class($object)][$object->id()]);
	else
		$GLOBALS['bors_data']['cached_objects4'][get_class($object)][$object->id()] = $object;
}

function class_internal_uri_load($uri)
{
	if(!preg_match("!^(\w+)://(.*)$!", $uri, $m))
		return NULL;

	$class_name = $m[1];

	if(substr($m[2],-1) == '/' && preg_match('!^(\d+)/$!', $m[2], $mm))
		$id = $mm[1];
	else
		$id = $m[2];

	$page = NULL;
	if(preg_match("!^(.+),(\d+)$!", $id, $m))
	{
		$id = $m[1];
		$page = $m[2];
	}

	return object_init($class_name, $id, array('page'=>$page));
}

function class_load($class, $id = NULL, $args=array())
{
	if(preg_match("!^\w+$!", $class))
		return object_init($class, $id, $args);

	if(preg_match("!^/!", $class) && bors()->server()->host())
		$class = 'http://'.bors()->server()->host().$class;

	if(!is_object($id) && preg_match("!^(\d+)/$!", $id, $m))
		$id = $m[1];

	if(preg_match("!^(\w+)://.+!", $class, $m))
	{
		if(preg_match("!^http://!", $class))
		{
			// Фиксим некорректные ссылки с форумов, например, оканчивающиеся на «.html,»
			$class = preg_replace('/[\.,\)\]!\?…"\']+$/', '', $class);

			if(preg_match('!^(.+)#(.+)$!', $class, $m))
				$class = $m[1];

			if($obj = class_load_by_url($class, $args))
				return $obj;

			if($obj = class_internal_uri_load($class, $args))
				return $obj;
		}
		elseif($obj = class_internal_uri_load($class, $args))
			return $obj;
	}

	return NULL;
}

function class_load_by_url($url, $args)
{
	if($obj = class_load_by_vhosts_url($url, $args))
		return $obj;

	return class_load_by_local_url($url, $args);
}

function try_object_load_by_map($url, $url_data, $check_url, $check_class, $match, $url_pattern, $skip)
{
//	if(config('is_developer'))
//		echo "<hr/><small>$skip: try_object_load_by_map($url, ".print_r($url_data, true).", $check_url, $check_class, ".print_r($match, true).")<br/><Br/></small>\n";

	debug_log_var('try_object_load_by_map.url_pattern', $url_pattern);
	debug_log_var('try_object_load_by_map.check_class', $check_class);

	$id = NULL;
	$page = NULL;

	if(preg_match("!^redirect:(.+)$!", $check_class, $m))
	{
		$check_class = $m[1];
		$redirect = true;
	}
	else
		$redirect = false;

	$args = array(
		'match' => $match,
		'called_url' => $url
	);

	// Формат вида aviaport_image_thumb(3,geometry=2)
	if(preg_match("!^(.+) \( (\d+|NULL)( , [^)]+=[^)]+ )+ \)$!x", $check_class, $m))
	{
		foreach(explode(',', $m[3]) as $pair)
		{
			if(preg_match('!^(\w+)=(.+)$!', $pair, $mm))
			{
				// Если число, то это номер группировки URL,
				// если строка - то она присваивается непосредственно.
				if(is_numeric($mm[2]))
					$args[$mm[1]] = $match[$mm[2]+$skip];
				else
					$args[$mm[1]] = $mm[2];
			}
		}

		$check_class = $m[1];
		$id = ($m[2] == 'NULL') ? NULL : $match[$m[2]+$skip];

		$page = $args;
	}
	elseif(preg_match("!^(.+)\((\d+|NULL),(\d+|NULL)\)$!", $check_class, $m))
	{
		$check_class = $m[1];
		$id = $m[2] == 'NULL' ? NULL : $match[$m[2]+$skip];
		$page = @$match[$m[3]+$skip];
	}
	elseif(preg_match("!^(.+)\((\w+)\)$!", $check_class, $class_match))
	{
		$check_class = $class_match[1];
/*
		if(config('debug_mode'))
		{
			echo "skip=$skip, url_pattern=$url_pattern<br/>\n";
			print_d($class_match);
			print_d($match);
		}
*/
		switch($class_match[2])
		{
			case 'url':
				$id = $url;
				break;
			default:
				$id = is_numeric($class_match[2]) ? $match[$class_match[2]+$skip] : $class_match[2];
				break;
		}

		if($check_class == 'include')
		{
			// Подгрузка блока расширений карты привязок URL.
			$class_base = $id;
			$found = false;
			$map_file_new = '/classes/'.str_replace('_', '/', $class_base).'/url_map.php';
			$map_file_old = '/classes/'.str_replace('_', '/', $class_base).'/bors_map.php';
			foreach(bors_dirs() as $dir)
			{
				if(file_exists($dir.$map_file_old))
				{
					require_once($dir.$map_file_old);
					$found = true;
				}
				if(file_exists($dir.$map_file_new))
				{
					require_once($dir.$map_file_new);
					$found = true;
				}
			}

//			if(!$found)
//				bors_throw(ec('Для ссылки ').$url.(' отсутствует файл блока расширений карты привязок "').$map_file_new.'"');

			if(empty($GLOBALS['bors_url_submap_map']))
				return NULL;

			foreach($GLOBALS['bors_url_submap_map'] as $pair)
			{
				if(!preg_match('!^(.*)\s*=>\s*(.+)$!', $pair, $m))
					exit(ec("Ошибка формата bors_url_submap [$map_file]: '{$pair}'"));

				$m[1] = trim($m[1]);
				if(preg_match('!^\(/(.+)$!', $m[1], $mfoo))
					$url_subpattern = '('.$match[2].'/'.$mfoo[1];
				else
					$url_subpattern = $match[2].$m[1];

				$class_path = trim($m[2]);
				if($class_path[0] == '_')
					$class_path  = $class_base.$class_path;

				$check_url = $url_data['scheme'].'://'.$url_data['host'].(empty($url_data['port'])?'':':'.$url_data['port']).$url_data['path'];
				if(preg_match('!\?!', $url_subpattern) && !empty($url_data['query']))
					$check_url .= '?'.$url_data['query'];

//				echo "<small>Check $url_subpattern to $url for <b>{$class_path}</b> as<br/>&nbsp; &nbsp; &nbsp; &nbsp; !^http://({$url_data['host']}[^/]*){$url_subpattern}\$! to {$check_url}</small><br />\n";
				if(preg_match("!^http://({$url_data['host']}".(empty($url_data['port'])?'':':'.$url_data['port'])."[^/]*)$url_subpattern$!i", $check_url, $submatch))
				{
//					echo "+<br/>";
					if(($obj = try_object_load_by_map($url, $url_data, $check_url, $class_path, $submatch, $url_subpattern, 1)))
						return $obj;
				}
			}

			return NULL;
		}
	}

	if(preg_match("!^(.+)/([^/]+)$!", $check_class, $m))
		$class = $m[2];
	else
		$class = $check_class;

	if(is_array($page))
		$args = array_merge($args, $page);
	else
		$args['page'] = $page;

	$obj = object_init($check_class, $id, $args);
//	echo "object_init($check_class, $id, $args) = ".print_dd($obj)."<br/>\n";
	if(!$obj)
		return NULL;

	if($obj->can_be_empty() || $obj->is_loaded())
	{
		if($redirect)
		{
			//TODO: ввести корректную отработку redirect:
			// Проверить на http://forums.airbase.ru/2008/06/t61976--ssylki-na-temy-po-proektam-korablej-i-sudov-prezhde-chem-nac.html
			// баг в том, что редиректит при простой инициализации объектов
			// Пробуем в роли времянки возвращать URL:
			if(config('__main_object_load', false))
				return $obj->url_ex($page);
/*
			if(!config('do_not_exit'))
			{
				echo "Redirect by $url_pattern";
				go($obj->url_ex($page), true);
				bors_exit("Redirect");
			}
			else
*/
			// Иначе — загружаем объект редиректа
			return object_load($obj->url_ex($page));
		}
		return $obj;
	}

	return NULL;
}

function class_load_by_local_url($url, $args)
{
	$obj = @$GLOBALS['bors_data']['classes_by_uri'][$url];

	if(!empty($obj) && empty($args['no_load_cache']))
		return $obj;

	if(empty($GLOBALS['bors_map']))
	{
		echo "Warning: empty bors_map<br />\n";
		return NULL;
	}

	$url_data = @parse_url($url);
	$check_url = $url_data['scheme'].'://'.$url_data['host'].(empty($url_data['port'])?'':':'.$url_data['port']).@$url_data['path'];
	$is_query = !empty($url_data['query']);
	$host_helper = "!^http://({$url_data['host']}".(empty($url_data['port'])?'':':'.$url_data['port'])."[^/]*)";

//	var_dump($GLOBALS['bors_map']);

	foreach($GLOBALS['bors_map'] as $pair)
	{
		if(!preg_match('!^(.*)\s*=>\s*(.+)$!', $pair, $match))
			exit(ec("Ошибка формата bors_map: {$pair}"));

		$url_pattern = trim($match[1]);
		$class_path  = trim($match[2]);

		if(strpos($url_pattern, '\\?') && $is_query)
			$test_url = $check_url . '?' . $url_data['query'];
		else
			$test_url = $check_url;

		if(config('debug_trace')) echo '<br/>regexp="'.$host_helper.$url_pattern.'$!i" for '.$test_url.'<br/>'.$check_url."<Br/>$url_pattern, class_path=$class_path<br/>";
		if(preg_match($host_helper.$url_pattern.'$!i', $test_url, $match))
		{
			if(($obj = try_object_load_by_map($url, $url_data, $test_url, $class_path, $match, $url_pattern, 1)))
				return $obj;
		}
	}
//	exit();

	return NULL;
}

function class_load_by_vhosts_url($url)
{
	$data = @parse_url($url);

	if(!$data || empty($data['host']))
	{
		debug_hidden_log('class-loader-errors', ec("Error. Try to load class for incorrect URL format: ").$url);
		return NULL;
	}

	$data['host'] = preg_replace('/^www\./', '', $data['host']);

	global $bors_data;

	$obj = @$bors_data['classes_by_uri'][$url];
	if(!empty($obj))
		return $obj;

	if(empty($bors_data['vhosts'][$data['host']]))
		return NULL;

	$host_data = $bors_data['vhosts'][$data['host']];

	$url_noq = $data['scheme'].'://'.$data['host'].@$data['path'];
	$query = @$data['query'];

	foreach($host_data['bors_map'] as $pair)
	{
		if(!preg_match('!^(.*)\s*=>\s*(.+)$!', $pair, $match))
			exit(ec("Ошибка формата bors_map[{$data['host']}]: {$pair}"));

		$url_pattern = trim($match[1]);
		$class_path  = trim($match[2]);

		if(strpos($url_pattern, '\\?') !== false)
			$check_url = $url_noq."?".$query;
		else
			$check_url = $url_noq;

//		if(config('is_developer')) echo "Check vhost $url_pattern to $url for $class_path -- !^http://({$data['host']}){$url_pattern}\$ (q=$query)!<br />\n";
		if(preg_match('!^\s*http://!', $url_pattern))
			$prefix = '';
		else
			$prefix = 'http://('.preg_quote($data['host']).')';

		if(preg_match("!^{$prefix}{$url_pattern}$!i", $check_url, $match))
		{
//			if(config('is_developer')) echo "found $class_path as $pair / !^{$prefix}{$url_pattern}$! to $check_url in <pre>".print_r($host_data['bors_site'], true)."</pre><br />\n";

			if(preg_match("!^redirect:(.+)$!", $class_path, $m))
			{
				$class_path = $m[1];
				$redirect = true;
			}
			else
				$redirect = false;

			$id = NULL;
			$page = NULL;

			// Формат вида aviaport_image_thumb(3,geometry=2)
			if(preg_match("!^(.+) \( (\d+|NULL)( , [^)]+=[^)]+ )+ \)$!x", $class_path, $m))	
			{
				$args = array();
				foreach(explode(',', $m[3]) as $pair)
				{
					if(preg_match('!^(\w+)=(.+)$!', $pair, $mm))
					{
						if(is_numeric($mm[2]))
							$args[$mm[1]] = $match[$mm[2]+1];
						else
							$args[$mm[1]] = $mm[2];
					}
				}

				$class_path = $m[1];
				$id = ($m[2] == 'NULL') ? NULL : $match[$m[2]+1];

				$page = $args;
			}
			if(preg_match("!^(.+)\((\d+|NULL),(\d+)\)$!", $class_path, $m))	
			{
				$class_path = $m[1];
				$id = $match[$m[2]+1];
				$page = @$match[$m[3]+1];
			}
			elseif(preg_match("!^(.+)\((\d+)\)$!", $class_path, $class_match))	
			{
				$class_path = $class_match[1];
				$id = $match[$class_match[2]+1];
			}

			if(preg_match("!^(.+)/([^/]+)$!", $class_path, $m))
				$class = $m[2];
			else
				$class = $class_path;

//			echo "$class_path($id) - $url<br/>";

			$args = array(
					'local_path' => $host_data['bors_local'],
					'match' => empty($match[2]) ? NULL : $match,
					'called_url' => $url,
			);

			if(is_array($page))
				$args = array_merge($args, $page);
			else
				$args['page'] = $page;

//			echo "Try to object_init($class_path, $id, $args) <br/>";
//			print_d(bors_dirs());
			$args['host'] = $data['host'];
			if($obj = object_init($class_path, $id, $args))
			{
//				echo "init $obj.<br />Save to bors_data['classes_by_uri'][$url] = $obj<br/>";
				$bors_data['classes_by_uri'][$url] = $obj;

				if($redirect)
				{
//					if(!config('do_not_exit'))
//					{
//						echo "Redirect by $url_pattern";
//						go($obj->url_ex($page), true);
//						exit("Redirect");
//					}
//					else
//						return go($obj->url_ex($page), true);
					$obj->set_attr('redirect_to', $obj->url_ex($page));
				}

				return $obj;
			}
		}
	}

	return NULL;
}

function object_init($class_name, $object_id, $args = array())
{
//	echo "object_init($class_name, $object_id, ".print_dl($args).")<br/>\n";
//	if(config('is_debug')) debug_hidden_log('debug', "Try to load $class_name($object_id)");
	// В этом методе нельзя использовать debug_test()!!!

	$obj = NULL;
	$original_id = $object_id;

	if($object_id === 'NULL')
		$object_id = NULL;

	if(!($class_file = bors_class_loader::load($class_name, $args)))
	{
		if(config('throw_exception_on_class_not_found'))
			return bors_throw("Class '$class_name' not found");

		return $obj;
	}

	$found = 0;

	if(method_exists($class_name, 'id_prepare'))
		$object_id = call_user_func(array($class_name, 'id_prepare'), $object_id, $class_name, $args);

	if(is_object($object_id) && !is_object($original_id))
	{
		$obj = $object_id;
		$object_id = $obj->id();
		$found = 2;
	}
	elseif(empty($args['no_load_cache']))
	{
		$obj = &load_cached_object($class_name, $object_id, $args, $found);

		if($obj && ($obj->id() != $object_id))
		{
			$found = 0;
			delete_cached_object_by_id($class_name, $object_id);
			$obj = NULL;
		}
	}

	if(!$obj)
	{
		$found = 0;
		$obj = new $class_name($object_id);

		if(!method_exists($obj, 'set_class_file'))
			return NULL;

		$obj->set_class_file($class_file);

		if(config('debug_objects_create_counting_details'))
		{
			bors_function_include('debug/count_inc');
			debug_count_inc("bors_load($class_name,init)");
		}
	}

	unset($args['local_path']);
	unset($args['no_load_cache']);

	if(method_exists($obj, 'set_args') && $args)
		$obj->set_args($args);

	if($m = defval($args, 'match'))
		$obj->set_match($m);

	if($url = defval($args, 'called_url'))
	{
		$called_url = preg_replace('!\?$!', '', $url);
		if($port = bors()->server()->port())
			$called_url = preg_replace('!^(http://[^/]+)(/.*)$!', "$1:$port$2", $called_url);
//		echo "$called_url => ";
//		$called_url = preg_replace('!^http://'.preg_quote(bors()->server()->host(), '!').'/!', "/", $called_url);
//		echo "$called_url<br/>\n";
		$obj->set_called_url($called_url);
	}

	$obj->_configure();

	$loaded = $obj->is_loaded();

	if(is_object($loaded))
		$obj = $loaded;

	if(!$loaded)
		$loaded = $obj->data_load();

	if(/*($object_id || $url) && */!$obj->can_be_empty() && !$obj->is_loaded())
		return NULL;

	if(!empty($args['need_check_to_public_load']))
	{
		unset($args['need_check_to_public_load']);
		if(!method_exists($obj, 'can_public_load') || !$obj->can_public_load())
			return NULL;
	}

	if($found != 1 && $obj->can_cached())
		save_cached_object($obj);

	if(($map = config('objects_auto_convert')) 
			&& ($to_class = @$map[$obj->class_name()])
			&& ($data = $obj->__loaded_fields())
	)
	{
		if(!empty($obj->changed_fields))
			foreach($obj->changed_fields as $field => $property)
				$data[$field] = $obj->$property;

		$obj->store();
		$x = object_new($to_class);
		$x->set_called_url($obj->called_url(), false);
		foreach($data as $key => $value)
			$x->{"set_$key"}($value, true);
		$x->new_instance();
		$obj->delete();
	}

	return $obj;
}

function bors_objects_preload(&$objects, $field, $preload_class, $store_field = NULL)
{
	if(!$objects)
		return array();

	$ids = array();
	foreach($objects as $x)
		$ids[$x->$field()] = 1;

	if(!array_keys($ids))
		return array();

	$targets = bors_find_all($preload_class, array('id IN' => array_keys($ids), 'by_id' => !!$store_field));

	if($store_field)
		foreach($objects as $x)
			$x->set_attr($store_field, @$targets[$x->$field()]);

	return $targets;
}

function bors_objects_targets_preload($objects, $target_class_field = 'target_class_name', $target_id_field = 'target_object_id', $store_field = 'target')
{
	$ids = array();
	foreach($objects as $x)
		@$ids[$x->$target_class_field()][$x->$target_id_field()] = true;

	$targets = array();
	foreach($ids as $target_class => $oids)
		$targets[$target_class] = objects_array($target_class, array('id IN' => array_keys($oids), 'by_id' => !!$store_field));

	if($store_field)
		foreach($objects as $x)
			$x->set_attr($store_field, @$targets[$x->$target_class_field()][$x->$target_id_field()]);

	return $targets;
}
