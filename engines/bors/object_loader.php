<?php

function class_include($class_name, &$args = array())
{
	if($file_name = @$GLOBALS['bors_data']['class_included'][$class_name])
		return $file_name;

	if(in_array($class_name, config('classes_skip', array())))
		return false;

	$class_path = "";
	$class_file = $class_name;

	if(preg_match("!^(.+/)([^/]+)$!", str_replace("_", "/", $class_name), $m))
	{
		$class_path = $m[1];
		$class_file = $m[2];
	}

	foreach(bors_dirs() as $dir)
	{
//		echo "check {$dir}/classes/{$class_path}{$class_file}.php  <br/>\n";
		if(file_exists($file_name = "{$dir}/classes/{$class_path}{$class_file}.php"))
		{
			require_once($file_name);
			$GLOBALS['bors_data']['class_included'][$class_name] = $file_name;
			return $file_name;
		}

		if(file_exists($file_name = "{$dir}/classes/bors/{$class_path}{$class_file}.php"))
		{
			require_once($file_name);
			$GLOBALS['bors_data']['class_included'][$class_name] = $file_name;
			return $file_name;
		}

		if(file_exists($file_name = "{$dir}/classes/inc/$class_name.php"))
		{
			require_once($file_name);
			$GLOBALS['bors_data']['class_included'][$class_name] = $file_name;
			return $file_name;
		}
	}

	if(class_exists($class_name))
		return class_include(get_parent_class($class_name));

	if(empty($args['host']))
		return false;

	$data = bors_vhost_data($args['host']);
	if(file_exists($file_name = "{$data['bors_site']}/classes/{$class_path}{$class_file}.php"))
	{
		require_once($file_name);
		$GLOBALS['bors_data']['class_included'][$class_name] = $file_name;
		$args['need_check_to_public_load'] = true;
		return $file_name;
	}

	if(file_exists($file_name = "{$data['bors_site']}/classes/bors/{$class_path}{$class_file}.php"))
	{
		require_once($file_name);
		$GLOBALS['bors_data']['class_included'][$class_name] = $file_name;
		$args['need_check_to_public_load'] = true;
		return $file_name;
	}

	return false;
}

spl_autoload_register('class_include');

function bors_object_caches_drop() { unset($GLOBALS['bors_data']['cached_objects4']); }

function &load_cached_object($class_name, $id, $args, &$found=0)
{
//		$GLOBALS['bors_data']['cached_objects4'][get_class($object)][$object->id()] = &$object;
	$obj = NULL;

	if(is_object($id) || @$args['no_load_cache'])
		return $obj;

	if(!empty($GLOBALS['bors_data']['cached_objects4'][$class_name][$id]))
	{
		$obj = &$GLOBALS['bors_data']['cached_objects4'][$class_name][$id];
		$updated = false;
		if(config('object_loader_filemtime_check'))
			$updated = !method_exists($obj, 'class_filemtime') || filemtime($obj->real_class_file()) > $obj->class_filemtime();

//		if(config('is_developer'))
//			echo "Found in memory <b>$class_name</b>('$id'); can_cached={$obj->can_cached()}; updated = $updated (me=".method_exists($obj, 'class_filemtime')."; ".filemtime($obj->real_class_file()).' > '.$obj->class_filemtime().")<br />";
//		echo "load {$class_name}($id), nocache=".@$args['no_load_cache']."<br/>\n";

		if($obj->can_cached() && !$updated)
		{
			$found = 1;
			return $obj;
		}
	}

	if(($memcache = config('memcached_instance')) && call_user_func(array($class_name, 'can_cached')))
	{
		debug_count_inc('memcached checks');
		$hash = 'bors_v'.config('memcached_tag').'_'.$class_name.'://'.$id;
		if($x = unserialize($memcache->get($hash)))
		{
			$updated = false;
			if(config('object_loader_filemtime_check'))
				$updated = !method_exists($x, 'class_filemtime') || filemtime($x->real_class_file()) > $x->class_filemtime();

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

function save_cached_object(&$object, $delete = false, $use_memcache = true)
{
	if(!method_exists($object, 'id') || is_object($object->id()))
		return;

	if($use_memcache && ($memcache = config('memcached_instance')) && $object->can_cached())
	{
		$hash = 'bors_v'.config('memcached_tag').'_'.get_class($object).'://'.$object->id();

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
		$GLOBALS['bors_data']['cached_objects4'][get_class($object)][$object->id()] = &$object;
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

	if(preg_match("!^/!", $class))
		$class = 'http://'.$_SERVER['HTTP_HOST'].$class;

	if(!is_object($id) && preg_match("!^(\d+)/$!", $id, $m))
		$id = $m[1];

	if(preg_match("!^(\w+)://.+!", $class, $m))
	{
		if(preg_match("!^http://!", $class))
		{
//			echo "Try load $class<Br/>\n";
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
//	if(config('debug_mode'))
//		echo "<hr/><small>$skip: try_object_load_by_map($url, ".print_r($url_data, true).", $check_url, $check_class, ".print_r($match, true).")<br/><Br/></small>\n";

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
	elseif(preg_match("!^(.+)\((\d+|NULL),(\d+)\)$!", $check_class, $m))
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
			require_once($map_file = 'classes/'.str_replace('_', '/', $class_base).'/bors_map.php');
			foreach($GLOBALS['bors_url_submap_map'] as $pair)
			{
				if(!preg_match('!^(.*)\s*=>\s*(.+)$!', $pair, $m))
					exit(ec("Ошибка формата bors_url_submap [$map_file]: '{$pair}'"));

				$url_subpattern = $match[2].trim($m[1]);
				$class_path = trim($m[2]);
				if($class_path[0] == '_')
					$class_path  = $class_base.$class_path;

				$check_url = $url_data['scheme'].'://'.$url_data['host'].(empty($url_data['port'])?'':':'.$url_data['port']).$url_data['path'];
				if(preg_match('!\?!', $url_subpattern) && !empty($url_data['query']))
					$check_url .= '?'.$url_data['query'];

//				echo "<small>Check $url_subpattern to $url for <b>{$class_path}</b> as !^http://({$url_data['host']}[^/]*){$url_subpattern}\$! to {$check_url}</small><br />\n";
				if(preg_match("!^http://({$url_data['host']}".(empty($url_data['port'])?'':':'.$url_data['port'])."[^/]*)$url_subpattern$!i", $check_url, $submatch))
					if(($obj = try_object_load_by_map($url, $url_data, $check_url, $class_path, $submatch, $url_subpattern, 1)))
						return $obj;
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

//	echo "object_init($check_class, $id)<br />";
	if(($obj = object_init($check_class, $id, $args))
		&& ($obj->can_be_empty() || $obj->loaded())
	)
	{
		if($redirect)
		{
			if(!config('do_not_exit'))
			{
				echo "Redirect by $url_pattern";
				go($obj->url($page), true);
				bors_exit("Redirect");
			}
			else
				return object_load($obj->url($page));
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

	foreach($GLOBALS['bors_map'] as $pair)
	{
		if(!preg_match('!^(.*)\s*=>\s*(.+)$!', $pair, $match))
			exit(ec("Ошибка формата bors_map: {$pair}"));

		$url_pattern = trim($match[1]);
		$class_path  = trim($match[2]);

		$check_url = $url_data['scheme'].'://'.$url_data['host'].(empty($url_data['port'])?'':':'.$url_data['port']).$url_data['path'];
		if(strpos($url_pattern, '\\?') && !empty($url_data['query']))
			$check_url .= '?'.$url_data['query'];

		if(preg_match("!^http://({$url_data['host']}".(empty($url_data['port'])?'':':'.$url_data['port'])."[^/]*)$url_pattern$!i", $check_url, $match))
		{
			if(($obj = try_object_load_by_map($url, $url_data, $check_url, $class_path, $match, $url_pattern, 1)))
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

	global $bors_data;

	$obj = @$bors_data['classes_by_uri'][$url];
	if(!empty($obj))
		return $obj;

	if(empty($bors_data['vhosts'][$data['host']]))
		return NULL;

	$host_data = $bors_data['vhosts'][$data['host']];

	$url_noq = $data['scheme'].'://'.$data['host'].$data['path'];
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

//		echo "Check vhost $url_pattern to $url for $class_path -- !^http://({$data['host']}){$url_pattern}\$ (q=$query)!<br />\n";
		if(preg_match('!^\s*http://!', $url_pattern))
			$prefix = '';
		else
			$prefix = 'http://('.preg_quote($data['host']).')';

		if(preg_match("!^{$prefix}{$url_pattern}$!i", $check_url, $match))
		{
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
					if(!config('do_not_exit'))
					{
						echo "Redirect by $url_pattern";
						go($obj->url($page), true);
						exit("Redirect");
					}
					else
						return object_load($obj->url($page));
				}

				return $obj;
			}
		}
	}

	return NULL;
}

function object_init($class_name, $object_id, $args = array())
{
	// В этом методе нельзя использовать debug_test()!!!

	$obj = NULL;
	$original_id = $object_id;

	if($object_id === 'NULL')
		$object_id = NULL;

	if(!($class_file = class_include($class_name, $args)))
		return $obj;

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
//		echo "cache loaded: $obj<br/>\n";
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
			debug_count_inc('init object '.$class_name);
	}

	unset($args['local_path']);
	unset($args['no_load_cache']);

	if(method_exists($obj, 'set_args') && $args)
		$obj->set_args($args);

	if($m = defval($args, 'match'))
		$obj->set_match($m);

	if($url = defval($args, 'called_url'))
		$obj->set_called_url(preg_replace('!\?$!', '', $url));

	$obj->_configure();

	$loaded = $obj->loaded();

	if(is_object($loaded))
		$obj = $loaded;

	if(!$loaded)
		$loaded = $obj->init();

	if(/*($object_id || $url) && */!$obj->can_be_empty() && !$obj->loaded())
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

function bors_objects_preload($objects, $field, $preload_class, $store_field = NULL)
{
	$ids = array();
	foreach($objects as $x)
		$ids[$x->$field()] = 1;

	$targets = objects_array($preload_class, array('id IN' => array_keys($ids), 'by_id' => !!$store_field));

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
