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

function load_cached_object($class_name, $id, $args)
{
	if(is_object($id))
		return NULL;
	
	if(@$args['no_load_cache'])
		return NULL;
			
//	if($class_name == 'bors_tools_search' && bors()->user() && bors()->user()->id() == 10000) debug_trace();
//	if($class_name == 'bors_tools_search' && bors()->user() && bors()->user()->id() == 10000) echo "load_cached_object($class_name, $id, $args)<br />";
		
	if($obj = @$GLOBALS['bors_data']['cached_objects4'][$class_name][$id])
	{
//		echo "Found in memory <b>$class_name</b>('$id'); can_cached={$obj->can_cached()}<br />";
		if($obj->can_cached())
			return $obj;
	}

//	echo "Try load $class_name($id) from memcached<br />";
		
	if(($memcache = config('memcached_instance')) && !is_object($id))
	{
//		$memcache = &new Memcache;
//		$memcache->connect(config('memcached')) or debug_exit("Could not connect memcache");

		if($x = @$memcache->get('bors_v'.config('memcached_tag').'_'.$class_name.'://'.$id))
		{
//			echo "<b>got!</b><br />";
			if($x->can_cached())
			{
				$x->wakeup();
				return $x;
			}
		}
	}

	return NULL;
}

function delete_cached_object($object) { return save_cached_object($object, true); }

function save_cached_object(&$object, $delete = false)
{
	if(!method_exists($object, 'id') || is_object($object->id()))
		return;

//	echo "Try store memcached {$object->internal_uri()} with can_cached={$object->can_cached()}; config('memcached')=".config('memcached')."<br />";
	if(($memcache = config('memcached_instance')) && $object->can_cached())
	{
//		$memcache = &new Memcache;
//		$memcache->connect(config('memcached')) or debug_exit("Could not connect memcache");
				
		$hash = 'bors_v'.config('memcached_tag').'_'.get_class($object).'://'.$object->id();
			
//		echo "Store $hash<br/>";
		
		$object->sleep();

		if($delete)
			@$memcache->delete($hash);
		else
			@$memcache->set($hash, $object, true, 600);
		
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

		$id = $m[2];
		$page = NULL;
		if(preg_match("!^(.+),(\d+)$!", $id, $m))
		{
			$id = $m[1];
			$page = $m[2];
		}

		return object_init($class_name, $id, array('page'=>$page));
	}

	function pure_class_load($class_name, $id, $args, $local_path = NULL)
	{
		if(is_string($id) && ($id == 'NULL'))
			$id = NULL;

		if(!($class_file = class_include($class_name, $local_path)))
			return NULL;

		if($obj = load_cached_object($class_name, $id, $args))
		{
		 	if(empty($args['no_load_cache']))
				return $obj;
			
			$obj->store();
//			$obj->cache_clean_self();
		}

		$obj = &new $class_name($id);

		$obj->set_class_file($class_file);
			
		return $obj;
	}

	function class_load($class, $id = NULL, $args=array())
	{
		if(preg_match("!^/!", $class))
			$class = 'http://'.$_SERVER['HTTP_HOST'].$class;
	
		if(!is_object($id) && preg_match("!^(\d+)/$!", $id, $m))
			$id = $m[1];
	
		if(preg_match("!^(\w+)://.+!", $class, $m))
		{
			if(preg_match("!^http://!", $class))
			{
//				echo "Load $class<br/>\n";
			
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

		if(preg_match("!^\w+$!", $class))
			return object_init($class, $id, $args);
		else
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
		
		$url_data = parse_url($url);

		foreach($GLOBALS['bors_map'] as $pair)
		{
			if(!preg_match('!^(.*)\s*=>\s*(.+)$!', $pair, $match))
				exit(ec("Ошибка формата bors_map: {$pair}"));
			
			$url_pattern = trim($match[1]);
			$class_path  = trim($match[2]);

//			echo "Initial url=$url<br/>\n";

			if(preg_match('!\?!', $url_pattern) && !preg_match('!\?!', $url) && !empty($_SERVER['QUERY_STRING']))
				$check_url = $url."?".$_SERVER['QUERY_STRING'];
			else
				$check_url = $url;
			
//			echo "<small>Check $url_pattern to $url for <b>{$class_path}</b> as !^http://({$url_data['host']}[^/]*){$url_pattern}\$! to {$check_url}</small><br />\n";
			if(preg_match("!^http://({$url_data['host']}[^/]*)$url_pattern$!", $check_url, $match))
			{
//				echo "<b>Ok - $class_path</b><br />"; exit();
				
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

					
				if($obj = object_init($class_path, $id, $args))
				{
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
						'use_cache' => true,
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

function object_init($class_name, $object_id, $args = array())
{
	if(config('debug_class_search_track'))
		echo "<small>object_init($class_name, $object_id,...)</small><br/>\n";

	// В этом методе нельзя исползовать debug_test()!

	if(!is_array($args))
		$args = $args ? array('page' => $args) : array();

//	if($class_name == 'bors_tools_search' && bors()->user() && bors()->user()->id() == 10000) echo "object_init($class_name, $object_id)<br />";
	$obj = pure_class_load($class_name, $object_id, $args, $local_path = defval($args, 'local_path'));

	if(!$obj)
		return NULL;

	if(method_exists($obj, 'set_page'))
	{
		if(empty($args['page']))
			$obj->set_page($obj->default_page());
		else
		{
			if(is_numeric($args['page']))
				$args['page'] = intval($args['page']);

			$obj->set_page($args['page']);
		}
	}

	$use_cache = defval($args, 'use_cache', true);
	unset($args['local_path']);
	unset($args['use_cache']);
	unset($args['no_load_cache']);

	if(method_exists($obj, 'set_args'))
		$obj->set_args($args);
		
	if($m = defval($args, 'match'))
		$obj->set_match($m);

	if($url = defval($args, 'called_url'))
		$obj->set_called_url(preg_replace('!\?$!', '', $url));

	if(!$obj->loaded())
		$obj->init();

//	if(method_exists($obj, 'id')) echo "{$obj->class_name()}({$obj->id()}) loaded = {$obj->loaded()}<br />\n";
//	else echo get_class($obj)." already inited<br />";

	if($obj->is_only_tuner())
		return NULL;
	
//	echo $obj->class_name()."; cf=obj->class_file(); object_id=$object_id; url=$url; cbe={$obj->can_be_empty()}; ld={$obj->loaded()}<br />\n";
	
	if(($object_id || $url)
		&& method_exists($obj, 'can_be_empty')
		&& !$obj->can_be_empty()
		&& !$obj->loaded() 
//		&& $obj->storage_engine() 
	)
		return NULL;

//	echo "{$class_name}($object_id) was loaded seccessfully} as ".get_class($obj)."<br />\n"; // exit();

	if($use_cache)
		save_cached_object($obj);
		
	return $obj;
}
