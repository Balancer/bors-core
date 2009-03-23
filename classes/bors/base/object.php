<?php

class base_object extends base_empty
{
	var $_loaded = false;
	function loaded() { return $this->_loaded; }
	function set_loaded($value = true) { return $this->_loaded = $value; }

	var $match;
	function set_match($match) { return $this->match = $match;	}

	private $_parents = false;
	function set_parents($array) { return $this->_parents = $array;	}
	function parents($exact = false)
	{
		if($this->_parents !== false)
			return $this->_parents;

		if($exact)
			return $this->_parents = array();

		return $this->_parents = array(
			empty($this->match[2]) ? "http://{$this->match[1]}/" : "http://{$this->match[1]}{$this->match[2]}"
		);
	}

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
		$r_db = NULL;
		$r_table = NULL;
		$r_id_field = NULL;
		$r_db_field = NULL;
	
		foreach($this->fields() as $db => $tables)
		{
			foreach($tables as $table => $fields)
			{
				if(preg_match('!^(\w+)\((\w+)\)$!', $table, $m))
				{
					$table = $m[1];
					$id_field = $m[2];
				}

				foreach($fields as $property => $db_field)
				{
					if(is_numeric($property))
						$property = $db_field;

//					if(preg_match('/^(\w+)\|.+/', $db_field, $m))
//						$db_field = $m[1];

					if($property == $test_property)
						list($r_db, $r_table, $r_db_field) = array($db, $table, $db_field);

					if($property == 'id')
						$r_id_field = $db_field;

					if($r_id_field && $r_db_field)
						return array($r_db, $r_table, $r_id_field, $r_db_field);
				}
			}
		}

		return false;
	}

	private $config;
	function _configure()
	{
		if($config = $this->config_class())
		{
			$this->config = object_load($config, $this);
			//TODO: workaround странной ошибки на страницах вида http://balancer.ru/user/29251/aliases.html
			//Call to undefined method airbase_forum_config::set() in /var/www/.bors/bors-core/classes/bors/base/config.php on line 13
			get_class($this);
			if(!$this->config)
				debug_exit("Can't load config ".$this->config_class());
		}
	}

	function init($data_load = true)
	{
		if(!$data_load)
			return false;
		
		if($storage_engine = $this->storage_engine())
		{
			$storage_engine = object_load($storage_engine, NULL, array('no_load_cache' => true));
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
	function local_data() { return $this->local_template_data_set(); }
	function local_template_data_set() { return array(); }
	function local_template_data_array() { return $this->template_data; }

	function set_template_data($data, $db_up)
	{
		foreach($data as $x)
			if(preg_match('/(.+)=(.+)/', $x, $m))
				$this->add_global_template_data(trim($m[1]), trim($m[2]));
	}

	function add_global_template_data($var_name, $value) { return set_global_template_var($var_name, $value); }
	function global_template_data_set() { return array(); }
	function global_template_data_array() { return global_template_vars(); }

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
			
			debug_trace();
			exit("__call[".__LINE__."]: undefined method '$method' for class '".get_class($this)."'");
		}

		$field_storage = "field_{$field}_storage";

		//TODO: сделать более жёсткой проверку на setting
		if(!$setting && config('strict_auto_fields_check')
			&& !method_exists($this, $field_storage)
			&& empty($params[2]) // При установке из ORM Без проверки на тип!
			&& !$this->autofield($field)
			&& @$_SERVER['SVCNAME'] != 'tomcat-6' && !property_exists($this, "stb_{$field}")
		)
		{
			$auto_objs = $this->auto_objects();
			if($f = @$auto_objs[$field])
				if(preg_match('/^(\w+)\((\w+)\)$/', $f, $m))
					return $this->set($field, object_load($m[1], $this->$m[2]()), false);
		
			if(@$_SERVER['SVCNAME'] != 'tomcat-6')
				debug_trace();
			exit("__call[".__LINE__."]: undefined method '$method' for class '".get_class($this)."'");
		}

		if($setting)
			return $this->set($field, $params[0], $params[1]);
		else
			return $this->get_property($field);
	}

	function get_property($name)
	{
		$p="stb_{$name}";
		if(!config('strict_auto_fields_check')
				|| @$_SERVER['SVCNAME'] == 'tomcat-6' 
				|| property_exists($this, $p))
			return @$this->$p;
		
		debug_exit("Try to get undefined properties ".get_class($this).".$name");
		return NULL;
	}

	function preParseProcess() { return false; }
	function preShowProcess() { return false; }

	function pre_parse($get = array()) { return $this->preParseProcess($get); }
	function pre_show($get = array()) { return $this->preShowProcess($get); }

	private $__mutex;
	private function __mutex_lock()
	{
		if(!$this->__mutex)
		{
			$this->__mutex = sem_get(ftok($this->class_file(), 'm'));
			sem_acquire($this->__mutex);
		}
	}

	private function __mutex_unlock()
	{
		if($this->__mutex)
		{
			sem_release($this->__mutex);
			$this->__mutex = NULL;
		}
	}

	function set($field, $value, $db_update)
	{
		$field_name = "stb_$field";

		if($db_update && @$this->$field_name !== $value)
		{
			if(config('mutex_lock_enable'))
				$this->__mutex_lock();

			if(@$this->$field_name == $value && @$this->$field_name !== NULL && $value !== NULL)
				debug_hidden_log('types', 'type_mismatch: value='.$value.'; original type: '.gettype(@$this->$field_name).'; new type: '.gettype($value));

			//TODO: продумать систему контроля типов.
//			if(@$this->$field_name == $value && @$this->$field_name !== NULL && $value !== NULL)
//				debug_hidden_log('types', 'type_mismatch: value='.$value.'; original type: '.gettype(@$this->$field_name).'; new type: '.gettype($value));

			$this->changed_fields[$field] = $field_name;
			bors()->add_changed_object($this);
		}

		return $this->$field_name = $value;
	}

	function fset($field, $value, $db_update)
	{
		$field_name = "stb_$field";

		if($db_update && @$this->$field_name != $value)
		{
			if(config('mutex_lock_enable'))
				$this->__mutex_lock();
				
			$this->changed_fields[$field] = $field_name;
			bors()->add_changed_object($this);
		}

		return $this->$field_name = $value;
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

	var $stb_keywords_string = '';

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

		if($this->auto_assign_all_fields())
		{
			foreach($this->main_table_fields() as $property => $field)
			{
				if(is_numeric($property))
					$property = $field;
		
				$this->add_local_template_data($property, $this->$property());
			}
		}

		foreach($this->local_data() as $key => $value)
			$this->add_local_template_data($key, $value);

		foreach($this->global_template_data_set() as $key => $value)
			$this->add_global_template_data($key, $value);

		static $called = false; //TODO: в будущем снести вторые вызовы.
		if($called)
			return;

		$called = true;

		foreach($this->data_providers() as $key => $value)
			$this->add_template_data($key, $value);
	}

	function titled_url() { return '<a href="'.$this->url($this->page())."\">{$this->title()}</a>"; }

	function titled_url_ex($title=NULL, $append=NULL, $url_append='')
	{
		if($title===NULL)
			$title = $this->title();

		return '<a href="'.$this->url($this->page()).$url_append.'"'.($append?' '.$append:'').">{$title}</a>"; 
	}

	function nav_named_url() { return '<a href="'.$this->url($this->page())."\">{$this->nav_name()}</a>"; }
	function titled_admin_url($title = NULL)
	{
		if($title === NULL)
			$title = $this->title() ? $this->title() : '---';
		return '<a href="'.$this->admin_url($this->page()).'">'.$title.'</a>';
	}

	function titled_edit_url($title = NULL)
	{
		if($title === NULL)
			$title = $this->title() ? $this->title() : '---';
		return '<a href="'.$this->edit_url($this->page()).'">'.$title.'</a>';
	}

	function imaged_admin_link($title = NULL)
	{
		if($title === NULL)
			$title = ec('Администрировать ').strtolower($this->class_title_rp());
		return "<a href=\"{$this->admin_url($this->page())}\"><img src=\"/bors-shared/images/edit-16.png\" width=\"16\" height=\"16\" alt=\"edit\" title=\"$title\"/></a>";
}

	function imaged_edit_link($title = NULL) { return $this->imaged_edit_url($title); }
	function imaged_edit_url($title = NULL)
	{
		if($title === NULL)
			$title = ec('Редактировать ').strtolower($this->class_title_rp());
		return "<a href=\"{$this->edit_url($this->page())}\"><img src=\"/bors-shared/images/edit-16.png\" width=\"16\" height=\"16\" alt=\"edit\" title=\"$title\"/></a>";
	}

	function imaged_delete_link($text = NULL, $title = NULL) { return $this->imaged_delete_url($title, $text); }
	
	function imaged_delete_url($title = NULL, $text = '')
	{
		if($title == 'del')
			$title = ec('Удалить ').strtolower($this->class_title_rp());

		if($text === NULL)
			$text = $title;

		if($text)
			$text = '&nbsp;'.$text;

		return "<a href=\"{$this->delete_url()}\"><img src=\"/bors-shared/images/drop-16.png\" width=\"16\" height=\"16\" alt=\"del\" title=\"$title\"/>{$text}</a>";
	}

	private function _setdefaultfor_url($target_id, $field_for_def)  { return "/admin/tools/set-default/?object={$this->internal_uri()}&target_id={$target_id}&target_field=$field_for_def"; }
	function imaged_set_default_link($target_id, $field_for_def, $title = NULL)
	{
		if($title === NULL)
			$title = ec('Сделать выбранным по умолчанию');

		return "<a href=\"".$this->_setdefaultfor_url($target_id, $field_for_def)."\"><img src=\"/bors-shared/images/notice-16.gif\" width=\"16\" height=\"16\" alt=\"def\" title=\"$title\"/></a>";
	}


	function admin_delete_link()
	{
		return $this->imaged_delete_url(NULL, 'Удалить '.strtolower($this->class_title_rp()));
	}

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
//			print_d($array);
//			exit();
			foreach(explode(',', $array['time_vars']) as $var)
			{
				if(@$array["{$var}_month"] && @$array["{$var}_day"] && @$array["{$var}_year"])
				{
					$array[$var] = strtotime(intval(@$array["{$var}_year"])
						.'-'.intval(@$array["{$var}_month"])
						.'-'.intval(@$array["{$var}_day"])
						.' '.intval(@$array["{$var}_hour"])
						.':'.intval(@$array["{$var}_minute"])
						.':'.intval(@$array["{$var}_seconds"]).(@$array["{$var}_year"] >= 1970 ? ' +0200' : ' +0400'));
/*					echo intval(@$array["{$var}_year"])
						.'-'.intval(@$array["{$var}_month"])
						.'-'.intval(@$array["{$var}_day"])
						.' '.intval(@$array["{$var}_hour"])
						.':'.intval(@$array["{$var}_minute"])
						.':'.intval(@$array["{$var}_seconds"])."\n";
					echo $array[$var]."\n";
					echo date("r", $array[$var]);
*/					
					// mktime (@$array["{$var}_hour"], @$array["{$var}_minute"], @$array["{$var}_second"], @$array["{$var}_month"], @$array["{$var}_day"], @$array["{$var}_year"]);
				}
				else // Не полный формат даты, например, 2009-0-0 - пишем как строку.
					$array[$var] = intval(@$array["{$var}_year"]).'-'.intval(@$array["{$var}_month"]).'-'.intval(@$array["{$var}_day"]);

				if(empty($array["{$var}_month"]) && empty($array["{$var}_day"]) && empty($array["{$var}_year"]))
					$array[$var] = NULL;

				unset($array["{$var}_hour"], $array["{$var}_minute"], $array["{$var}_second"], $array["{$var}_month"], $array["{$var}_day"], $array["{$var}_year"]);
			}

//			print_d($array);
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

	function store($cache_clean = true)
	{
		if(!$this->id())
			return;

		if(empty($this->changed_fields))
			return;

		include_once('engines/search.php');

		if($cache_clean)
			$this->cache_clean();
			
		if(!($storage = $this->storage_engine()))
		{
			$storage = 'storage_db_mysql_smart';
//			debug_hidden_log('Not defined storage engine for '.$this->class_name());
		}
			
		$storage = object_load($storage);
				
		$storage->save($this);
		save_cached_object($this);

		if(config('search_autoindex') && $this->auto_search_index())
		{
			if(config('bors_tasks'))
				bors_tools_tasks::add_task($this, 'bors_task_index', 0, -10);
			else
				bors_search_object_index($this, 'replace');
		}
			
		bors()->drop_changed_object($this->internal_uri());
	}

	function replace_on_new_instance() { return false; }

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
		
			foreach(explode(' ', $this->autofields()) as $f)
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

		return NULL;
	}

	function fields() { return array(); }
	function auto_objects() { return array(); }

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

	function edit_url()  { return '/admin/edit-smart/?object='.urlencode($this->internal_uri()); }
	function admin_url() { return '/admin/?object='.urlencode($this->internal_uri()); }
	function admin_parent_url()
	{
		if($o = object_load($this->admin_url()))
			if($p = $o->parents())
				return $p[0];

		return @$_SERVER['HTTP_REFERER'];
	}

	function delete_url()
	{
		if($x = $this->has_smart_field('is_deleted')) //array($r_db, $r_table, $r_id_field, $r_db_field
			return '/admin/mark/delete/?object='.$this->internal_uri().'&ref='.$this->admin_parent_url(); 
		else
			return '/admin/delete/?object='.$this->internal_uri().'&ref='.$this->admin_parent_url(); 
	}

	var $_called_url;
	function set_called_url($url) { return $this->_called_url = $url; }
	function called_url() { return $this->_called_url; }
	
	var $stb_url_engine = 'url_calling';
	private $_url_engine = false;
	function url($page = NULL)
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

	function cache_static() { return 0; }
	function cache_static_expire() { return $this->cache_static() ? $this->cache_static() + time() : 0; }

	// Признак постоянного существования объекта.
	// Если истина, то объект создаётся не по первому запросу, а при сохранении
	// параметров и/или сбросе кеша, удалении старого статического кеша и т.п.
	// Применимо только при cache_static === true
	function cache_static_recreate() { return false; }
	function cache_static_can_be_dropped() { return true; }
//	var $stb_cache_static = 0;

	function cache_groups() { return ''; }
	function cache_groups_parent() { return ''; }

	function uid() { return md5($this->class_id().'://'.$this->id().','.$this->page()); }
	function can_cached() { return true; }

	protected $_dbh = NULL;
	function db($database_name = NULL)
	{
		if($this->_dbh === NULL)
			$this->_dbh = &new driver_mysql($database_name ? $database_name : $this->main_db());
			
		return $this->_dbh;
	}

	public function __sleep()
	{
		if(!$this->_dbh)
			return;
			
		$this->_dbh->close(); 
		$this->_dbh = NULL;

		return array_keys(get_object_vars($this));
	}

	function main_db() { return $this->main_db_storage(); }
	function main_db_storage() { return config('main_bors_db'); }
	function main_table(){ return $this->main_table_storage(); }
	function main_table_storage(){ return $this->class_name(); }
	function main_table_fields() { return array(); }
	function title_field() { $this->field_title_storage(); }
	function field_title_storage() { $f=$this->main_table_fields(); return @$f['title'].'(id)'; }

	function set_checkboxes($check_list, $db_up)
	{
		foreach(explode(',', $check_list) as $name)
		{
			if(method_exists($this, $method = 'set_'.$name))
				$this->$method(empty($_GET[$name]) ? 0 : 1, $db_up);
			else
				$this->fset($name, empty($_GET[$name]) ? 0 : 1, $db_up);
		}
	}

	function set_checkboxes_list($check_list, $db_up)
	{
		foreach(explode(',', $check_list) as $name)
			if(empty($_GET[$name]))
				$this->{'set_'.$name}(array(), $db_up);
	}

	private $args = array();
	function set_args($args) { return $this->args = $args; }
	function _set_arg($name, $value) { return $this->args[$name] = $value; }
	function args($name=false, $def = NULL) { return $name ? (isset($this->args[$name]) ? $this->args[$name] : $def) : $this->args; }

	function __toString() { return $this->class_name().'://'.$this->id().($this->page() > 1 ? ','.$this->page() : ''); }

	function was_cleaned() { return !empty($GLOBALS['bors_obect_self_cleaned'][$this->internal_uri()]); }
	function set_was_cleaned($value) { return $GLOBALS['bors_obect_self_cleaned'][$this->internal_uri()] = $value; }

	function cache_clean_self()
	{
		if($this->was_cleaned())
			return;

		$this->set_was_cleaned(true);


		if($this->cache_static() > 0 && $this->cache_static_can_be_dropped())
			cache_static::drop($this);

		// Чистка memcache и Cache.
		delete_cached_object($this);

		foreach(explode(' ', $this->cache_groups_parent()) as $group_name)
			if($group_name)
				foreach(objects_array('cache_group', array('cache_group' => $group_name)) as $group)
					if($group)
						$group->clean();
	}

	function cache_children() { return array(); }

	function cache_clean($clean_object = NULL)
	{
		if(!$clean_object)
			$clean_object = $this;

		$this->cache_clean_self();
		foreach($this->cache_children() as $child_cache)
			if($child_cache)
				$child_cache->cache_clean_self($clean_object);
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
		$data = url_parse($this->called_url());
//		print_d($data);

//		return $data['local_path'];
		return preg_match('!^(.+)/$!', $data['local_path'], $m) ? $m[1] : dirname($data['local_path']);
	
		//TODO: затычка!
//		return $_SERVER['DOCUMENT_ROOT'].preg_replace('!^http://[^/]+!', '', $this->called_url());
	}

	private $class_file;
	private $class_filemtime;
	function set_class_file($file_name)
	{
		$this->class_filemtime = filemtime($file_name);
		return $this->class_file = $file_name;
	}
	function class_filemtime() { return $this->class_filemtime; }
	function class_file() { return $this->class_file; }
	function real_class_file() { return $this->class_file; }
	function class_dir() { return dirname($this->class_file()); }

	function pre_set() { }
	function post_set() { }
	function on_new_instance() { }

	var $stb_sort_order;
//	private $sort_order = 0;
//	function sort_order() { return $this->sort_order; }
//	function set_sort_order($value) { return $this->sort_order = $value; }

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

	function direct_content()
	{
		if($render_engine = $this->render_engine())
		{
			if(!($re = object_load($render_engine)))
				debug_exit("Can't load global render engine {$render_engine} for object '{$this}'");

			return $re->render($this);
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

		if(preg_match('/viewforum.php/', $file))
		{
			debug_hidden_log('stat-cache', 'try to cache viewforum.php!');
			return NULL;
		}

		return $file;
	}

	function use_temporary_static_file() { return true; }

	function internal_charset() { return config('internal_charset', 'utf-8'); }
	function output_charset() { return config('output_charset', 'utf-8'); }
	function files_charset() { return config('files_charset', 'utf-8'); }
	function db_charset() { return config('db_charset', 'utf-8'); }

	function cs_f2i($str) { return iconv($this->files_charset(), $this->internal_charset().'//IGNORE', $str); }
	function cs_d2i($str) { return iconv($this->db_charset(), $this->internal_charset().'//IGNORE', $str); }
	function cs_i2d($str, $f='') { return iconv($this->internal_charset(), $this->db_charset().'//IGNORE', $str); }
	function cs_i2o($str)
	{
		if(preg_match('/koi8|cp866/i', $out_cs = $this->output_charset()))
		{
			$str = str_replace(
				array('«'      ,'»'),
				array('&laquo;','&raquo;'),
				$str);
		}

		$res = iconv($this->internal_charset(), $out_cs.'//IGNORE', $str);
		if($str && !$res)
			debug_hidden_log('iconv_error', $this->internal_uri().", {$this->internal_charset()} -> {$out_cs}: $str");
		
		return $res;
	}

	function content($can_use_static = true, $recreate = false)
	{
		$use_static = $recreate || ($can_use_static && config('cache_static') && $this->cache_static() > 0);
		$file = $this->static_file();
		$fe = file_exists($file);
		$fs = $fe && filesize($file) > 2000;

		if($use_static && $file && $fe && !$recreate)
			return file_get_contents($this->static_file());

		if($use_static && !$fs && $this->use_temporary_static_file() && config('temporary_file_contents'))
			cache_static::save($this, str_replace(array(
				'$url',
				'$title',
			), array(
				$this->url($this->page()),
				$this->title(),
			), $this->cs_i2o(config('temporary_file_contents'))), 120);


		$content = $this->direct_content();

		if($this->internal_charset() != $this->output_charset())
			$content = $this->cs_i2o($content);

		if(!$content)
		{
			cache_static::drop($this);
			return '';
		}

		if($use_static || $recreate)
			cache_static::save($this, $content);

		return $content;
	}

	function show($object) { echo $this->get_content($object); }

	function object_title() { return $this->class_title().ec(' «').$this->title().ec('»'); }
	function object_titled_url() { return $this->class_title().ec(' «').$this->titled_url().ec('»'); }

	function cross_ids($to_class) { return bors_get_cross_ids($this, $to_class); }
	function cross_objs($to_class = '') { return bors_get_cross_objs($this, $to_class); }
	function cross_objects($to_class = '') { return bors_get_cross_objs($this, $to_class); }
	function add_cross($class, $id, $order = 0) { return bors_add_cross($this->class_id(), $this->id(), $class, $id, $order); }
	function add_cross_object($object, $order = 0) { return bors_add_cross_obj($this, $object, $order); }
	function cross_remove_object($obj) { bors_remove_cross_pair($this->class_id(), $this->id(), $obj->class_id(), $obj->id()); }

	function on_action_link($data)
	{
		if(empty($data['link_object_from']))
			$obj = $this;
		else
			$obj = object_load($data['link_object_from']);

		if(!$obj)
			return bors_exit(ec('Не существующий привязывающий объект ').$data['link_object_from']);

		$target = object_load($data['link_class_name'], $data['link_object_id']);

		if(!$target)
			return bors_exit(ec('Не существующий привязываемый объект ').$data['link_class_name'].'://'. $data['link_object_id']);

		$obj->add_cross_object($target);
	}

	function default_page() { return 1; }

	private $_page;
	function page() { return $this->_page; }
	function set_page($page) { return $this->_page = $page ? $page : $this->default_page(); }

	function empty_id_handler() { return NULL; }
	function auto_assign_all_fields() { return false; }
}
