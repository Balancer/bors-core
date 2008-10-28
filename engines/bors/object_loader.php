<?php

function class_include($class_name, $local_path = "")
{
	if($file_name = @$GLOBALS['bors_data']['class_included'][$class_name])
		return $file_name;
	
	$class_path = "";
	$class_file = $class_name;

	if(preg_match("!^(.+/)([^/]+)$!", str_replace("_", "/", $class_name), $m))
	{
		$class_path = $m[1];
		$class_file = $m[2];
	}

	foreach(bors_dirs() as $dir)
	{
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
	}

	if(class_exists($class_name))
		return class_include(get_parent_class($class_name), $local_path);
	
	return false;
}

function __autoload($class_name) { class_include($class_name); }

function &load_cached_object($class_name, $id, $args, &$found = 0)
{
	$obj = NULL;

	if(is_object($id) || @$args['no_load_cache'])
		return $obj;
	
	if(!empty($GLOBALS['bors_data']['cached_objects4'][$class_name][$id]))
	{
		$obj = &$GLOBALS['bors_data']['cached_objects4'][$class_name][$id];
		$updated = false;
		if(config('object_loader_filemtime_check'))
			$updated = !method_exists($obj, 'class_filemtime') || filemtime($obj->real_class_file()) > $obj->class_filemtime();

//		echo "Found in memory <b>$class_name</b>('$id'); can_cached={$obj->can_cached()}; updated = $updated (me=".method_exists($obj, 'class_filemtime')."; ".filemtime($obj->real_class_file()).' > '.$obj->class_filemtime().")<br />";

		if($obj->can_cached() && !$updated)
		{
			$found = 1;
			return $obj;
		}
	}

//	echo "Try load $class_name($id) from memcached<br />";
		
	if($memcache = config('memcached_instance'))
	{
		if($x = @$memcache->get('bors_v'.config('memcached_tag').'_'.$class_name.'://'.$id))
		{
			$updated = false;
			if(config('object_loader_filemtime_check'))
				$updated = !method_exists($x, 'class_filemtime') || filemtime($x->real_class_file()) > $x->class_filemtime();

//			echo "Found in memcache <b>{$x->class_name()}</b>('{$x->id()}'); can_cached={$x->can_cached()}; updated = $updated (me=".method_exists($obj, 'class_filemtime')."; ".filemtime($x->real_class_file()).' > '.$x->class_filemtime().")<br />";

			if($x->can_cached() && !$updated)
			{
//				$x->wakeup();
//				$GLOBALS['bors_data']['cached_objects4'][get_class($x)][$x->id()] = $x;
				$found = 2;
				return $x;
			}
		}
	}

	$obj = NULL;
	return $obj;
}

function delete_cached_object($object) { return save_cached_object($object, true); }

function save_cached_object(&$object, $delete = false)
{
//	if(debug_is_balancer())
//		echo "Store $object<br/>\n";

	if(!method_exists($object, 'id') || is_object($object->id()))
		return;

	if(($memcache = config('memcached_instance')) && $object->can_cached())
	{
		$hash = 'bors_v'.config('memcached_tag').'_'.get_class($object).'://'.$object->id();

//		$object->sleep();

		if($delete)
			@$memcache->delete($hash);
		else
			@$memcache->set($hash, $object, true, rand(600, 1200));
		
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
//		echo "Load $url<br />\n";
	
		if($obj = class_load_by_vhosts_url($url, $args))
			return $obj;
		
		return class_load_by_local_url($url, $args);
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

//			echo "Initial url=$url<br/>\n";

			$check_url = $url_data['scheme'].'://'.$url_data['host'].$url_data['path'];
			if(preg_match('!\?!', $url_pattern) && !empty($url_data['query']))
				$check_url .= '?'.$url_data['query'];
			
//			if(debug_is_balancer())	echo "<small>Check $url_pattern to $url for <b>{$class_path}</b> as !^http://({$url_data['host']}[^/]*){$url_pattern}\$! to {$check_url}</small><br />\n";
			if(preg_match("!^http://({$url_data['host']}[^/]*)$url_pattern$!", $check_url, $match))
			{
//				echo "<b>Ok - $class_path</b><br />";
				
				$id = NULL;
				$page = NULL;
				
				if(preg_match("!^redirect:(.+)$!", $class_path, $m))
				{
					$class_path = $m[1];
					$redirect = true;
				}
				else
					$redirect = false;
				
				// Формат вида aviaport_image_thumb(3,geometry=2)
				if(preg_match("!^(.+) \( (\d+|NULL)( , [^)]+=[^)]+ )+ \)$!x", $class_path, $m))	
				{
					$args = array();
					foreach(split(',', $m[3]) as $pair)
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
					$id = $match[$m[2]+1];
					
					$page = $args;
				}
				elseif(preg_match("!^(.+)\((\d+|NULL),(\d+)\)$!", $class_path, $m))	
				{
					$class_path = $m[1];
					$id = $match[$m[2]+1];
					$page = @$match[$m[3]+1];
				}
				elseif(preg_match("!^(.+)\((\w+)\)$!", $class_path, $class_match))	
				{
					$class_path = $class_match[1];
					switch($class_match[2])
					{
						case 'url':
							$id = $url;
							break;
						default:
							$id = $match[$class_match[2]+1];
							break;
					}
				}

				$args = array('match'=>$match, 'called_url'=>$url);

				if(preg_match("!^(.+)/([^/]+)$!", $class_path, $m))
					$class = $m[2];
				else
					$class = $class_path;

				if(is_array($page))
					$args = array_merge($args, $page);
				else
					$args['page'] = $page;

//				echo "object_init($class_path, $id)<br />";
				if(($obj = object_init($class_path, $id, $args))
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
			}
		}
//		exit();

		return NULL;
}

function class_load_by_vhosts_url($url)
{
		$data = @parse_url($url);
		
		if(!$data || empty($data['host']))
		{
			debug_hidden_log('class-loader-errors', ec("Ошибка. Попытка загрузить класс из URL неверного формата: ").$url);
			return NULL;
		}
		
		global $bors_data;
		
		$obj = @$bors_data['classes_by_uri'][$url];
		if(!empty($obj))
			return $obj;
			
//		print_d($data); print_d($bors_data['vhosts']);

		if(empty($bors_data['vhosts'][$data['host']]))
			return NULL;

		$host_data = $bors_data['vhosts'][$data['host']];
		
		foreach($host_data['bors_map'] as $pair)
		{
			if(!preg_match('!^(.*)\s*=>\s*(.+)$!', $pair, $match))
				exit(ec("Ошибка формата bors_map[{$data['host']}]: {$pair}"));
			
			$url_pattern = trim($match[1]);
			$class_path  = trim($match[2]);

			if(preg_match("!\\\\\?!", $url_pattern))
				$check_url = $url."?".$_SERVER['QUERY_STRING'];
			else
				$check_url = $url;

//			echo "Check vhost $url_pattern to $url for $class_path -- !^http://({$_SERVER['HTTP_HOST']}){$url_pattern}\$!<br />\n";
			if(preg_match("!^http://(\Q{$data['host']}\E)$url_pattern$!", $check_url, $match))
			{
//				echo "Found: $class_path for  $check_url<br />";
			
				if(preg_match("!^redirect:(.+)$!", $class_path, $m))
				{
					$class_path = $m[1];
					$redirect = true;
				}
				else
					$redirect = false;
			
				$id = NULL;
				$page = 0;
				
				// Формат вида aviaport_image_thumb(3,geometry=2)
				if(preg_match("!^(.+) \( (\d+|NULL)( , [^)]+=[^)]+ )+ \)$!x", $class_path, $m))	
				{
					$args = array();
					foreach(split(',', $m[3]) as $pair)
						if(preg_match('!^(\w+)=(.+)$!', $pair, $mm))
							$args[$mm[1]] = $match[$mm[2]+1];

					$class_path = $m[1];
					$id = $match[$m[2]+1];
					
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

//				echo "$class_path($id) - $url";
				
				$args = array(	
						'local_path' => $host_data['bors_local'],
						'match' => empty($match[2]) ? NULL : $match,
						'called_url' => $url,
				);
				
				if(is_array($page))
					$args = array_merge($args, $page);
				else
					$args['page'] = $page;


				if($obj = object_init($class_path, $id, $args))
				{
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

function &object_init($class_name, $object_id, $args = array())
{
	if(config('debug_class_search_track'))
		echo "<small>object_init($class_name, $object_id,...)</small><br/>\n";

	// В этом методе нельзя исползовать debug_test()!!!

	$obj = NULL;

	if($object_id === 'NULL')
		$object_id = NULL;

	if(!($class_file = class_include($class_name, defval($args, 'local_path'))))
		return $obj;

	$found = 0;
	if(empty($args['no_load_cache']))
		$obj = &load_cached_object($class_name, $object_id, $args, $found);
	
	if(!$obj)
	{
		$obj = &new $class_name($object_id);
		$obj->set_class_file($class_file);
	}
	
	if(empty($args['page']))
	{
		if(method_exists($obj, 'set_page'))
			$obj->set_page($obj->default_page());
	}
	else
	{
		if(is_numeric($args['page']))
			$args['page'] = intval($args['page']);

		$obj->set_page($args['page']);
	}

	unset($args['local_path']);
	unset($args['no_load_cache']);

	if(method_exists($obj, 'set_args'))
		$obj->set_args($args);
		
	if($m = defval($args, 'match'))
		$obj->set_match($m);

	if($url = defval($args, 'called_url'))
		$obj->set_called_url(preg_replace('!\?$!', '', $url));

	if(!$obj->loaded())
		$obj->init();

	if(($object_id || $url) && !$obj->can_be_empty() && !$obj->loaded())
	{
		$obj = NULL;
		return $obj;
	}
	
	if($found != 1 && $obj->can_cached())
		save_cached_object($obj);
		
	return $obj;
}
