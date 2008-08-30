<?php

class base_object extends base_empty
{
	var $_loaded = false;
	function loaded() { return $this->_loaded; }
	function set_loaded($value = true) { return $this->_loaded = $value; }

	var $match;
	function set_match($match) { return $this->match = $match;	}

	function parents() { return array(empty($this->match[2]) ? "http://{$this->match[1]}/" : "http://{$this->match[1]}{$this->match[2]}"); }
	var $stb_children = array();

	function rss_body()
	{
		if($body = $this->description())
			return $this->lcml($body);
		
		if($body = $this->source())
			return $this->lcml($body);
		
		return $this->body();
	}

	function rss_title() { return $this->title(); }

	function has_smart_field($test_property)
	{
		foreach($this->fields() as $db => $tables)
		{
			foreach($tables as $table => $fields)
			{
				if(preg_match('!^(\w+)\((\w+)\)$!', $table, $m))
				{
					$table = $m[1];
					$id_field = $m[2];
				}
				else
					$id_field = 'id';
				foreach($fields as $property => $db_field)
				{
					if(is_numeric($property))
						$property = $db_field;
					
					if($property == $test_property)
						return array($db, $table, $id_field, $db_field);
				}
			}
		}
		
		return false;
	}

	function __construct($id)
	{
		parent::__construct($id);

		foreach($this->fields() as $db => $tables)
		{
			foreach($tables as $tables => $fields)
			{
				foreach($fields as $property => $db_field)
				{
					if(is_numeric($property))
						$property = $db_field;
					
					$this->{'stb_'.$property} = "";
				}
			}
		}		

	}

	private $config;

	function init()
	{
		if($config = $this->config_class())
		{
			$this->config = object_load($config, $this);
			if(!$this->config)
				debug_exit("Can't load config ".$this->config_class());
		}
		
		if($storage_engine = $this->storage_engine())
		{
			$storage_engine = object_load($storage_engine);
			if(!$storage_engine)
				debug_exit("Can't load storage engine '{$this->storage_engine()}' in ".join(",<br/>\n", bors_dirs()));
			elseif($storage_engine->load($this) !== false || $this->can_be_empty())
				$this->_loaded = true;
		}

		if(!$this->config && ($config = $this->config_class()))
		{
			$this->config = object_load($config, $this);
			if(!$this->config)
				debug_exit("Can't load config ".$this->config_class());
		}

		if($data_provider = $this->data_provider())
			object_load($data_provider, $this)->fill();

		return false;
	}

	function lcml($text)
	{
		if(!$text)
			return;
	
		$ch = &new Cache();
		if($ch->get('base_object-lcml', $text) && 0)
			return $ch->last();

		return $ch->set(lcml($text,
			array(
				'cr_type' => $this->cr_type(),
				'sharp_not_comment' => $this->sharp_not_comment(),
				'html_disable' => $this->html_disable(),
		)), 7*86400);
	}

	function sharp_not_comment() { return true; }
	function html_disable() { return true; }

	private $_class_id;
	function class_id()
	{
		if(empty($this->_class_id))
			$this->_class_id = class_name_to_id($this);

		return $this->_class_id;
	}

	function class_title() { return get_class($this); }
	function class_title_rp() { return $this->class_title(); }

	static function add_template_data($var_name, $value) { return $GLOBALS['cms']['templates']['data'][$var_name] = $value; }
	
	static function template_data($var_name) { return @$GLOBALS['cms']['templates']['data'][$var_name]; }

	private $template_data = array();
	function add_local_template_data($var_name, $value) { return $this->template_data[$var_name] = $value; }
	function local_template_data_set() { return array(); }
	function local_template_data_array() { return $this->template_data; }

	private $global_template_data = array();
	function add_global_template_data($var_name, $value) { return $this->global_template_data[$var_name] = $value; }
	function global_template_data_set() { return array(); }
	function global_template_data_array() { return $this->global_template_data; }

	static function add_template_data_array($var_name, $value)
	{
		if(preg_match('!^(.+)\[(.+)\]$!', $var_name, $m))
			$GLOBALS['cms']['templates']['data'][$m[1]][$m[2]] = $value;
		else
			$GLOBALS['cms']['templates']['data'][$var_name][] = $value;
	}

	function __call($method, $params)
	{
		if(preg_match('!^autofield!', $method))
			return NULL;
//			debug_exit(ec("Неопределённый метод $method в классе ".get_class($this)));
	
		$field   = $method;
		$setting = false;
		if(preg_match('!^set_(\w+)$!', $method, $match))
		{
			$field   = $match[1];
			$setting = true;
		}

		if(preg_match('!^field_(\w+)_storage$!', $method, $match))
		{
			if($field = $this->autofield($match[1]))
				return $field;
			
			echo "<xmp>";
			debug_print_backtrace();
			echo "</xmp>";
			exit("__call[".__LINE__."]: undefined method '$method' for class '".get_class($this)."'");
		}
		
		$field_storage = "field_{$field}_storage";

		if(!method_exists($this, $field_storage) && !$this->autofield($field) && !property_exists($this, "stb_{$field}"))
		{
			echo "<xmp>";
			debug_print_backtrace();
			echo "</xmp>";
			exit("__call[".__LINE__."]: undefined method '$method' for class '".get_class($this)."'");
		}

		if($setting)
			return $this->set($field, $params[0], $params[1]);
		else
			return $this->get_property($field);
	}

	function get_property($name)
	{
		if(property_exists($this, $p="stba_{$name}"))
			return $this->$p;

		if(property_exists($this, $p="stb_{$name}"))
			return $this->$p;
		
		debug_exit("Try to get undefined properties ".get_class($this).".$name");
	}

	function preParseProcess() { return false; }
	function preShowProcess() { return false; }

	function pre_parse($get = array())
	{
		return $this->preParseProcess($get);
	}
	
	function pre_show($get = array()) { return $this->preShowProcess($get); }

	function set($field, $value, $db_update)
	{
//		echo "set ".get_class($this).".{$field} = $value<br/>\n";
			
		$field_name = "stba_$field";
		if(!property_exists($this, $field_name))
			$field_name = "stb_$field";

//		if(!property_exists($this, $field_name))
//			debug_exit("Try to set undefined properties ".get_class($this).".$field");

		if($db_update && @$this->$field_name != $value)
		{
//			echo "<xmp>Set {$field_name} from {$this->$field_name} to {$value}</xmp>\n";
			$this->changed_fields[$field] = $field_name;
			bors()->add_changed_object($this);
		}

		return $this->$field_name = $value;
	}

	function fset($field, $value, $db_update)
	{
		$field_name = "stb_$field";

		if($db_update && $this->$field_name != $value)
		{
			$this->changed_fields[$field] = $field_name;
			bors()->add_changed_object($this);
		}

		$this->$field_name = $value;
	}

	function render_engine() { return false; }
	function is_cache_disabled() { return true; }
	function template_vars() { return 'body source'; }
	function template_local_vars() { return 'create_time description id modify_time nav_name title'; }

	var $stb_create_time = NULL;
	function set_create_time($unix_time, $db_update) { $this->set("create_time", intval($unix_time), $db_update); }
	function create_time($exactly = false)
	{
		if($exactly || $this->stb_create_time)
			return $this->stb_create_time;

		if($this->stb_modify_time)
			return $this->stb_modify_time;

		return time(); 
	}

	var $stb_modify_time = NULL;
	function set_modify_time($unix_time, $db_update) { $this->set("modify_time", $unix_time, $db_update); }
	function modify_time($exactly = false)
	{
		if($exactly || $this->stb_modify_time)
			return $this->stb_modify_time;

		return time(); 
	}

	var $stb_title = '';
	function title() { return $this->stb_title; }
	function set_title($new_title, $db_update) { $this->set("title", $new_title, $db_update); }

	var $stb_description = NULL;
	function set_description($description, $db_update) { $this->set("description", $description, $db_update); }
	function description() { return $this->stb_description; }

	var $stb_nav_name = NULL;
	function set_nav_name($nav_name, $db_update) { $this->set("nav_name", $nav_name, $db_update); }
	function nav_name() { return !empty($this->stb_nav_name) ? $this->stb_nav_name : $this->title(); }

	var $stb_template = NULL;
	function set_template($template, $db_update) { $this->set("template", $template, $db_update); }
	function template() { return $this->stb_template ? $this->stb_template : config('default_template'); }

	function template_data_fill()
	{
		if($this->config)
			$this->config->template_init();

		foreach($this->local_template_data_set() as $key => $value)
			$this->add_local_template_data($key, $value);

		static $called = false; //TODO: в будущем снести вторые вызовы.
		if($called)
			return;

		$called = true;
		
//		echo ":::"; print_d($this->data_providers());
		foreach($this->data_providers() as $key => $value)
		{
			$this->add_template_data($key, $value);
		}
	}

	function cache_static() { return 0; }
//	var $stb_cache_static = 0;
	
	function titled_url($append=NULL) { return '<a href="'.$this->url($this->page()).'"'.($append?' '.$append:'').">{$this->title()}</a>"; }
	function nav_named_url() { return '<a href="'.$this->url($this->page())."\">{$this->nav_name()}</a>"; }
	function titled_admin_url() { return '<a href="'.$this->admin_url($this->page()).'">'.($this->title()?$this->title():'---').'</a>'; }
	function titled_edit_url() { return '<a href="'.$this->edit_url($this->page()).'">'.($this->title()?$this->title():'---').'</a>'; }

	function check_data(&$data)
	{
		foreach($data as $key => $val)
			if(!$this->check_value($key, $val))
				return true;

		return false;
	}

	function set_fields($array, $db_update_flag, $fields_list = NULL, $check_values = false)
	{
		//TODO: заюзать make_input_time? (funcs/datetime.php)
		if(!empty($array['time_vars']))
		{
			foreach(explode(',', $array['time_vars']) as $var)
			{
				$array[$var] = mktime (@$array["{$var}_hour"], @$array["{$var}_minute"], @$array["{$var}_second"], @$array["{$var}_month"], @$array["{$var}_day"], @$array["{$var}_year"]);
				if(empty($array["{$var}_year"]))
					$array[$var] = NULL;
				unset($array["{$var}_hour"], $array["{$var}_minute"], $array["{$var}_second"], $array["{$var}_month"], $array["{$var}_day"], $array["{$var}_year"]);
			}
		}	

		if($check_values && $this->check_data($data) === true)
			return false;
				
		if($fields_list)
		{
			foreach(explode(' ', $fields_list) as $key)
			{
				$method = "set_$key";
				$this->$method(@$array[$key], $db_update_flag);
			}
		}
		else
		{
			foreach($array as $key => $val)
			{
				$method = "set_$key";
//				echo "Set $key to $val<br />";
				if(method_exists($this, $method) || $this->autofield($key) || $this->has_smart_field($key))
					$this->$method($val, $db_update_flag);
			}
		}

		return true;
	}

	function check_value($field, $value)
	{
		$cond = $this->check_value_conditions();
		if(!($assert = @$cond[$field]))
			return true;
			
		if(preg_match('!^(.+)\|(.+?)$!', $assert, $m))
		{
			$assert  = $m[1];
			$message = $m[2];
		}
		else
			$message = ec('Ошибка параметра ').$field;

		eval("\$res = ('".addslashes($value)."' $assert);");
		if(!$res)
		{
			bors_message($message);
			return false;
		}
		
		return true;
	}

	function check_value_conditions() { return array(); }

	function store()
	{
		if(!$this->id())
			return;
//			$this->new_instance();
		
		bors()->changed_save();
	}

	function data_provider() { return NULL; }
	function data_providers() { return array(); }


	var $_autofields;
	function autofield($field)
	{
		if(method_exists($this, $method = "field_{$field}_storage"))
			return $this->$method();

		if(empty($this->_autofields))
		{
			$_autofields = array();
		
			foreach(split(' ', $this->autofields()) as $f)
			{
				$id	  = 'id';
				if(preg_match('!^(\w+)\((\w+)\)(.*?)$!', $f, $match))
				{
					$f  = $match[1].$match[3];
					$id = $match[2];
				}

				$name = $f;
				if(preg_match('!^(\w+)\->(\w+)$!', $f, $match))
				{
					$f    = $match[1];
					$name = $match[2];
				}
				$this->_autofields[$name] = "{$f}({$id})";
			}
		}
		
		if($res = @$this->_autofields[$field])
			return $res;

		if(property_exists($this, $p = "stbf_{$field}"))
		{
//			echo "={$p}=<br/>\n";
			if(preg_match('!^\w+$!', $this->$p))
				return "{$this->$p}({$this->id_field()})";
			else
				return $this->$p;
		}
			
		if(property_exists($this, "stba_{$field}"))
			return "{$field}({$this->id_field()})";

		return NULL;
	}
	
	function fields() { return array(); }
	
	function storage() { return object_load($this->storage_engine()); }

	var $stb_access_engine = NULL;
	var $stb_config_class = NULL;
	
	function access()
	{
		$access = $this->access_engine();
		if(!$access)
			$access = config('access_default', 'access_base');
		
		return object_load($access, $this);
	}

	function edit_url()  { return '/admin/edit/?object='.$this->internal_uri(); }
	function admin_url() { return '/admin/?object='.$this->internal_uri(); }
	function delete_url()  { return '/admin/delete/?object='.$this->internal_uri(); }

	var $_called_url;
	function set_called_url($url) { return $this->_called_url = $url; }
	function called_url() { return $this->_called_url; }
	
	var $stb_url_engine = 'url_calling';
	private $_url_engine = false;
	function url($page=1)
	{
		if(!$this->_url_engine || !$this->_url_engine->id())
		{
			$this->_url_engine = object_load($this->url_engine(), $this);
			if(!$this->_url_engine)
				debug_exit("Can't load url engine {$this->url_engine()} for class {$this}");
		}
		
		return $this->_url_engine->url($page);
	}

	function internal_uri()
	{
		if(preg_match("!^http://!", $this->id()))
			return $this->id();

		return  $this->class_name().'://'.$this->id().'/'; 
	}

	// Признак постоянного существования объекта.
	// Если истина, то объект создаётся не по первому запросу, а при сохранении
	// параметров и/или сбросе кеша, удалении старого статического кеша и т.п.
	// Применимо только при cache_static === true
	function permanent() { return false; }

	function cache_groups() { return ''; }
	function cache_groups_parent() { return ''; }

	function uid() { return md5($this->class_id().'://'.$this->id().','.$this->page()); }
	function can_cached() { return true; }

	protected $_dbh = NULL;
	function db($database_name = NULL)
	{
		if($this->_dbh === NULL)
			$this->_dbh = &new driver_mysql($database_name ? $database_name : $this->main_db_storage());
			
		return $this->_dbh;
	}

	function main_db_storage() { return config('main_bors_db'); }
	function main_table_storage(){ return $this->class_name(); }
	function main_table_fields() { return array(); }

	function set_checkboxes($check_list, $db_up)
	{
		foreach(split(',', $check_list) as $name)
			$this->{'set_'.$name}(empty($_GET[$name]) ? 0 : 1, $db_up);
	}

	function set_checkboxes_list($check_list, $db_up)
	{
		foreach(split(',', $check_list) as $name)
			if(empty($_GET[$name]))
				$this->{'set_'.$name}(array(), $db_up);
	}

	private $args = array();
	function set_args($args) { $this->args = $args; }
	function args($name=false, $def = NULL) { return $name ? (isset($this->args[$name]) ? $this->args[$name] : $def) : $this->args; }

	function __toString() { return $this->class_name().'://'.$this->id().($this->page() > 1 ? ','.$this->page() : ''); }

	function cache_clean_self()
	{
		if($this->cache_static() > 0)
			cache_static::drop($this);

		delete_cached_object($this);

		if(method_exists($this, 'cache_groups_parent'))
			foreach(explode(' ', $this->cache_groups_parent()) as $group_name)
				foreach(objects_array('cache_group', array('cache_group' => $group_name)) as $group)
					if($group)
						$group->clean();
	}

	function cache_children() { return array(); }

	function cache_clean($clean_object = NULL)
	{
		global $cleaned;
		
		if(!$clean_object)
			$clean_object = $this;

		if(empty($cleaned))
			$cleaned = array();
				
		$this->cache_clean_self($clean_object);
		foreach($this->cache_children() as $child_cache)
			if($child_cache && empty($cleaned[$child_cache->internal_uri()]))
			{
				$cleaned[$child_cache->internal_uri()] = 1;
				$child_cache->cache_clean($clean_object);
			}
	}

	function touch() { }

	function visits_counting() { return false; }
	function visits_inc($inc = 1, $time = NULL)
	{
		if(!$this->visits_counting())
			return;
	
		if($time === NULL)
			$time = time();
			
		if(!$this->first_visit_time())
			$this->set_first_visit_time($time, true);

		$this->set_visits($this->visits() + $inc, true);
		$this->set_last_visit_time($time, true);
	}

	function pre_action() { return false; }
	function need_access_level() { return 0; }
	function cache_life_time() { return 0; }

	function dir()
	{
		//TODO: затычка!
		return $_SERVER['DOCUMENT_ROOT'].preg_replace('!^http://[^/]+!', '', $this->called_url());
	}

	private $class_file;
	function set_class_file($file_name) { return $this->class_file = $file_name; }
	function class_file() { return $this->class_file; }
	function class_dir() { return dirname($this->class_file()); }

	function post_set() { }
	
	private $sort_order = 0;
	function sort_order() { return $this->sort_order; }
	function set_sort_order($value) { return $this->sort_order = $value; }

	function static_get_cache() { return false; }

	function change_time($exactly = false)
	{
		$changed = max($this->create_time(true), $this->modify_time(true));
		return $changed || $exactly ? $changed : time();
	}

	function extends_class() { return $this->class_name(); }

	private $extends_class_id;
	function extends_class_id()
	{
		if(empty($this->extends_class_id))
			$this->extends_class_id = class_name_to_id($this->extends_class());

		return $this->extends_class_id;
	}

	var $stb_owner_id = NULL;
	function owner() { return NULL; }

	function direct_content()
	{
		if($render_engine = $this->render_engine())
		{
			$re = object_load($render_engine);
			if(!$re)
				debug_exit("Can't load render engine {$render_engine} for class {$this}");
			$page = $this->page();
			$content = $re->render($this);
			$this->set_page($page);
			return $content;
		}

	    require_once('engines/smarty/bors.php');
		$this->template_data_fill();
		return template_assign_bors_object($this, NULL, true);
	}

	function index_file() { return 'index.html'; }

	function static_file()
	{
		$path = $this->url($this->page());
		$data = url_parse($path);

		$file = @$data['local_path'];
		if(preg_match('!/$!', $file))
			$file .= $this->index_file();

		return $file;
	}

	function use_temporary_static_file() { return true; }

	function content($can_use_static = true)
	{
		$use_static = $can_use_static && config('cache_static') && $this->cache_static() > 0;
		$file = $this->static_file();
		$fe = file_exists($file);
		$fs = $fe && filesize($file) > 2000;

		if($use_static && $file && $fe)
			return file_get_contents($this->static_file());

		if($use_static && !$fs && $this->use_temporary_static_file() && config('temporary_file_contents'))
			cache_static::save($this, str_replace(array(
				'$url',
				'$title',
			), array(
				$this->url($this->page()),
				$this->title(),
			), ec(config('temporary_file_contents'))), 120);
	
		$content = $this->direct_content($this);

		if($use_static)
			cache_static::save($this, $content);

		return $content;
	}
		
	function show($object) { echo $this->get_content($object); }
	
	function object_title() { return $this->class_title().ec(' «').$this->title().ec('»'); }
	function object_titled_url() { return $this->class_title().ec(' «').$this->titled_url().ec('»'); }
	
	function cross_objs($to_class = '') { return bors_get_cross_objs($this, $to_class); }
	function add_cross($class, $id, $order) { return bors_add_cross($this->class_id(), $this->id(), $class, $id, $order); }
}
