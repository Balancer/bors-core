<?php

class base_object extends base_empty
{
	var $data = array();

	function attr_preset() { return array(
			'config_class' => '',
			'access_engine' => '',
			'url_engine' => 'url_calling',
	); }

	private $__loaded = false;
	function loaded() { return $this->__loaded; }
	function set_loaded($value = true) { return $this->__loaded = $value; }

	private $__match;
	function set_match($match) { return $this->__match = $match; }

	function parents($exact = false)
	{
		if(($ps = @$this->data['parents']))
			return $ps;

		if($exact)
			return $this->data['parents'] = array();

		if(empty($this->__match[2]))
			$parent = secure_path(dirname($this->called_url()).'/');
		else
			$parent = "http://{$this->__match[1]}{$this->__match[2]}";

		return $this->data['parents'] = array($parent);
	}

	function rss_body()
	{
		if(($body = $this->description()))
			return $this->lcml($body);

		if(($body = $this->source()))
			return $this->lcml($body);

		return $this->body();
	}

	function rss_title() { return $this->title(); }
	function rss_url() { return $this->url(); }

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
		foreach($this->attr_preset() as $attr => $val)
			$this->attr[$attr] = $val;

		if(($config = $this->config_class()))
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

		if(($storage_engine = $this->storage_engine()))
		{
			$storage_engine = object_load($storage_engine, NULL, array('no_load_cache' => true));
			if(!$storage_engine)
				debug_exit("Can't load storage engine '{$this->storage_engine()}' for class <b>{$this->class_name()}</b><br/>at {$this->class_file()}  in dirs:<br/>".join(",<br/>\n", bors_dirs()));
			elseif($storage_engine->load($this) !== false || $this->can_be_empty())
				$this->__loaded = true;
		}

		if(!$this->config && ($config = $this->config_class()))
		{
			$this->config = object_load($config, $this);
			if(!$this->config)
				debug_exit("Can't load config ".$this->config_class());
		}

		if(($data_provider = $this->data_provider()))
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

	function class_title()    { return ec('Объект ').@get_class($this); }	// Именительный: Кто? Что?
	function class_title_rp() { return ec('объекта ').get_class($this); }	// РодительныйГенитивКого? Чего?
	function class_title_dp() { return ec('объекту ').get_class($this); }	// Дательный Кому? Чему?
	function class_title_vp() { return ec('объект ').get_class($this); }	// Винительный Кого? Что?
	function class_title_tp() { return ec('объектом ').get_class($this); }	// Творительный Кем? Чем?
	function class_title_pp() { return ec('объекте ').get_class($this); }	// Предложный О ком? О чём?

	static function add_template_data($var_name, $value) { return $GLOBALS['cms']['templates']['data'][$var_name] = $value; }

	static function template_data($var_name) { return @$GLOBALS['cms']['templates']['data'][$var_name]; }

	private $template_data = array();
	function add_local_template_data($var_name, $value)
	{
		if(strpos($var_name, '['))
			return $this->add_template_data_array($var_name, $value);
		else
			return $this->template_data[$var_name] = $value;
	}

	function local_data() { return $this->local_template_data_set(); }
	function global_data() { return $this->global_template_data_set(); }
	function local_template_data_set() { return array('me' => bors()->user()); }
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

	function strict_auto_fields_check() { return config('strict_auto_fields_check'); }
	function __call($method, $params)
	{
		// Это был вызов $obj->set_XXX($value, $db_up)
		if(preg_match('!^set_(\w+)$!', $method, $match))
			return $this->set($match[1], $params[0], $params[1]);

		// Проверяем нет ли уже загруженного значения данных объекта
		if(array_key_exists($method, $this->data))
			return $this->data[$method];

		// Проверяем нет ли уже загруженного значения атрибута (временных несохраняемых данных) объекта
		if(array_key_exists($method, $this->attr))
			return $this->attr[$method];

		// Проверяем автоматические объекты.
		$auto_objs = $this->auto_objects();
		if(($f = @$auto_objs[$method]))
			if(preg_match('/^(\w+)\((\w+)\)$/', $f, $m))
				return $this->attr[$method] = object_load($m[1], $this->$m[2]());

		// Автоматические целевые объекты (имя класса задаётся)
		$auto_targs = $this->auto_targets();
		if(($f = @$auto_targs[$method]))
			if(preg_match('/^(\w+)\((\w+)\)$/', $f, $m))
				return $this->attr[$method] = object_load($this->$m[1](), $this->$m[2]());

		if($this->strict_auto_fields_check())
			debug_exit("__call[".__LINE__."]: undefined method '$method' for class '<b>".get_class($this)."({$this->id()})</b>'<br/>at {$this->class_file()}");

		return NULL;
	}

	function pre_parse() { return false; }
	function pre_show() { return false; }

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
		if($db_update && @$this->data[$field] !== $value)
		{
			if(config('mutex_lock_enable'))
				$this->__mutex_lock();

//			if(@$this->$field_name == $value && @$this->$field_name !== NULL && $value !== NULL)
//				debug_hidden_log('types', 'type_mismatch: value='.$value.'; original type: '.gettype(@$this->$field_name).'; new type: '.gettype($value));

			//TODO: продумать систему контроля типов.
//			if(@$this->$field_name == $value && @$this->$field_name !== NULL && $value !== NULL)
//				debug_hidden_log('types', 'type_mismatch: value='.$value.'; original type: '.gettype(@$this->$field_name).'; new type: '.gettype($value));

			$this->changed_fields[$field] = true;
			bors()->add_changed_object($this);
		}

		return $this->data[$field] = $value;
	}

	function render_engine() { return config('render_engine', false); }

	function is_cache_disabled() { return true; }
	function template_vars() { return 'body source me me_id'; }
	function template_local_vars() { return 'create_time description id modify_time nav_name title'; }

	function set_create_time($unix_time, $db_update) { return $this->set('create_time', intval($unix_time), $db_update); }
	function create_time($exactly = false)
	{
		if($exactly || !empty($this->data['create_time']))
			return @$this->data['create_time'];

		if(!empty($this->data['modify_time']))
			return $this->data['modify_time'];

		return time();
	}

	function set_modify_time($unix_time, $db_update) { return $this->set('modify_time', $unix_time, $db_update); }
	function modify_time($exactly = false)
	{
		if($exactly || !empty($this->data['modify_time']))
			return @$this->data['modify_time'];

		return time();
	}

	function title($exact = false) { return defval($this->data, 'title', $exact ? NULL : $this->class_name()); }
	function set_title($new_title, $db_update) { return $this->set('title', $new_title, $db_update); }

	function description() { return @$this->data['description']; }
	function set_description($description, $db_update) { return $this->set('description', $description, $db_update); }

	function nav_name() { return @$this->data['nav_name'] ? $this->data['nav_name'] : $this->title(); }
	function set_nav_name($nav_name, $db_update) { return $this->set('nav_name', $nav_name, $db_update); }

	function template() { return defval($this->data, 'template', defval($this->attr, 'template', config('default_template'))); }
	function set_template($template, $db_update) { $this->set('template', $template, $db_update); }

	function parents_string() { return join("\n", $this->parents());  }
	function set_parents_string($string, $dbup) { $this->set_parents(array_filter(explode("\n", $string)), $dbup); return $string;  }

	function children_string() { return ($cs = $this->children()) ? join("\n", $cs) : '';  }
	function set_children_string($string, $dbup) { $this->set_children(array_filter(explode("\n", $string)), $dbup); return $string;  }

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

		foreach($this->global_data() as $key => $value)
			$this->add_global_template_data($key, $value);

		foreach($this->local_data() as $key => $value)
			$this->add_local_template_data($key, $value);

		static $called = false; //TODO: в будущем снести вторые вызовы.
		if($called)
			return;

		$called = true;

		foreach($this->data_providers() as $key => $value)
			$this->add_template_data($key, $value);
	}

	function titled_url() { return '<a href="'.$this->url($this->page())."\">{$this->title()}</a>"; }
	function titled_link() { return $this->titled_url(); }

	function titled_target_link() { return $this->target()->titled_link(); }

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
			$title = ec('Администрировать ').bors_lower($this->class_title_rp());
		return "<a href=\"{$this->admin_url($this->page())}\"><img src=\"/_bors/images/edit-16.png\" width=\"16\" height=\"16\" alt=\"edit\" title=\"$title\"/></a>";
	}

	function imaged_edit_link($title = NULL) { return $this->imaged_edit_url($title); }
	function imaged_edit_url($title = NULL)
	{
		if($title === NULL)
			$title = ec('Редактировать ').bors_lower($this->class_title_rp());
		return "<a href=\"{$this->edit_url($this->page())}\"><img src=\"/_bors/i/edit-16.png\" width=\"16\" height=\"16\" alt=\"edit\" title=\"$title\"/></a>";
	}

	function titled_new_link($title = NULL)
	{
		if($title === NULL)
			$title = $this->title() ? $this->title() : '---';
		return '<a href="'.$this->new_url($this->page()).'">'.$title.'</a>';
	}

	function imaged_delete_link($text = NULL, $title = NULL) { return $this->imaged_delete_url($title, $text); }

	function imaged_delete_url($title = NULL, $text = '')
	{
		if($title == 'del')
			$title = ec('Удалить ').bors_lower($this->class_title_vp());

		if($text === NULL)
			$text = $title;

		if($text)
			$text = '&nbsp;'.$text;

		return "<a href=\"{$this->delete_url()}\"><img src=\"/_bors/images/drop-16.png\" width=\"16\" height=\"16\" alt=\"del\" title=\"$title\"/>{$text}</a>";
	}

	private function _setdefaultfor_url($target_id, $field_for_def)  { return "/admin/tools/set-default/?object={$this->internal_uri()}&target_id={$target_id}&target_field=$field_for_def"; }
	function imaged_set_default_link($target_id, $field_for_def, $title = NULL)
	{
		if($title === NULL)
			$title = ec('Сделать выбранным по умолчанию');

		return "<a href=\"".$this->_setdefaultfor_url($target_id, $field_for_def)."\"><img src=\"/_bors/i/notice-16.gif\" width=\"16\" height=\"16\" alt=\"def\" title=\"$title\"/></a>";
	}

	function admin_engine() { return config('admin_engine', 'bors_admin_engine'); }
	function admin() { return object_load($this->admin_engine(), $this); }

	function admin_delete_link()
	{
		return $this->imaged_delete_url(NULL, 'Удалить '.bors_lower($this->class_title_vp()));
	}

	// true if break
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
				if(@$array["{$var}_month"] && @$array["{$var}_day"] && @$array["{$var}_year"])
				{
					$array[$var] = strtotime(intval(@$array["{$var}_year"])
						.'-'.intval(@$array["{$var}_month"])
						.'-'.intval(@$array["{$var}_day"])
						.' '.intval(@$array["{$var}_hour"])
						.':'.intval(@$array["{$var}_minute"])
						.':'.intval(@$array["{$var}_seconds"]).(@$array["{$var}_year"] >= 1970 ? ' +0300' : ' +0400')); //TODO: мегакостыль!
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
		}

		if($check_values && $this->check_data($array) === true)
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
			if($array)
			{
				foreach($array as $key => $val)
				{
					$method = "set_$key";
					$this->$method($val, $db_update_flag);
				}
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

//		if($cache_clean)
//			$this->cache_clean();

		if(!($storage = $this->storage_engine()))
		{
			$storage = 'storage_db_mysql_smart';
//			debug_hidden_log('Not defined storage engine for '.$this->class_name());
		}

		$storage = object_load($storage);

		$storage->save($this);
		$this->__update_relations();
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

	private function __update_relations()
	{
		if(($rels = $this->_relations()))
		{
			foreach($rels as $field => $class)
			{
				if(preg_match('/^(\w+)$/', trim($class), $m))
					$rel_obj = object_load($m[1], $this->$field());
				elseif(preg_match('/^(\w+)\((\w+)\)$/', trim($class), $m))
					$rel_obj = objects_first($m[1], array($m[2] => $this->$field()));
				else
					exit("Unknown format: '$class'");

				if(!$rel_obj)
					exit("Unknown relation object '$class'");

				$rel_obj->set_modify_time($this->modify_time(), true);
//				$rel_obj->set_last_editor_id(@$this->data['last_editor_id'], true);
			}
		}
	}

	function replace_on_new_instance() { return false; }

	function data_provider() { return NULL; }
	function data_providers() { return array(); }

	function fields() { return array(); }
	function auto_objects() { return array(); }
	function auto_targets() { return array(); }

	function storage() { return object_load($this->storage_engine()); }

	function access()
	{
		$access = $this->access_engine();
		if(!$access)
			$access = config('access_default', 'access_base');

		return object_load($access, $this);
	}

	function edit_url()  { return '/_bors/admin/edit-smart/?object='.$this->internal_uri_ascii(); }
	function admin_url($exact = false) { return $exact ? NULL : '/_bors/admin/?object='.urlencode($this->internal_uri()); }
	function new_url()  { return '/_bors/admin/new-smart/?object='.urlencode($this->internal_uri()); }
	function admin_parent_url()
	{
		if(!preg_match('/'.preg_quote($this->admin_url(),'/').'/', bors()->main_object()->url()))
			return bors()->main_object()->url();

		if(($o = object_load($this->admin_url(true))))
			if($p = $o->parents())
				return $p[0];

		return @$_SERVER['HTTP_REFERER'];
	}

	function delete_url()
	{
		if(($x = $this->has_smart_field('is_deleted'))) //array($r_db, $r_table, $r_id_field, $r_db_field
			return '/admin/mark/delete/?object='.$this->internal_uri().'&ref='.$this->admin_parent_url(); 
		else
			return '/admin/delete/?object='.$this->internal_uri().'&ref='.$this->admin_parent_url(); 
	}

	function set_called_url($url) { return $this->attr['called_url'] = $url; }
	function called_url() { return @$this->attr['called_url']; }
	function called_url_no_get() { return preg_replace('/\?.+$/', '', @$this->attr['called_url']); }
	function _auto_redirect() { return true; }

	function url($page = NULL)
	{
		if(empty($this->attr['_url_engine_object'])/* || !$this->_url_engine->id() ?? */)
			if(!($this->attr['_url_engine_object'] = object_load($this->url_engine(), $this)))
				debug_exit("Can't load url engine {$this->url_engine()} for class {$this}");

		return $this->attr['_url_engine_object']->url($page);
	}

	function internal_uri_ascii($limit = false, $ignore_oversize = false)
	{
		if(is_object($id = $this->id()))
			$id = $this->id()->internal_uri_ascii();

		if(is_numeric($id))
			$uri = $this->class_name().'__'.$id;
		else
			$uri = $this->class_name().'__x'.base64_encode($id);

		if($limit && strlen($uri) > $limit)
		{
			if(!$ignore_oversize)
				debug_hidden_log('need-attention', 'too long ascii uri for '.$this->internal_uri());
			$uri = substr($uri, 0, $limit);
		}

		return $uri;
	}

	function internal_uri()
	{
		if(@preg_match("!^http://!", $this->id()))
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
	function title_field()
	{
		$f = $this->main_table_fields();
		return ($ft = @$f['title']) ? $ft : 'title';
	}

	function set_checkboxes($check_list, $db_up)
	{
		foreach(explode(',', $check_list) as $name)
		{
			if(method_exists($this, $method = 'set_'.$name))
				$this->$method(empty($_GET[$name]) ? 0 : 1, $db_up);
			else
				$this->set($name, empty($_GET[$name]) ? 0 : 1, $db_up);
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
	function args($name=false, $def = NULL) { return $name ? (array_key_exists($name, $this->args) ? $this->args[$name] : $def) : $this->args; }

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

	function document_root()
	{
		$data = url_parse($this->called_url());
		return $data['root'];
	}

	function host()
	{
		$data = url_parse($this->called_url());
		return $data['host'];
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

	function on_new_instance()
	{
		$this->__update_relations();
	}

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
		if(($render_engine = $this->render_engine()))
		{
			if($render_engine == 'self')
				$re = $this;
			elseif(!($re = object_load($render_engine)))
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
		$path = $this->url($this->args('page'));
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
	function input_charset() { return config('input_charset', $this->output_charset()); }
	function output_charset() { return config('output_charset', 'utf-8'); }
	function files_charset() { return config('files_charset', 'utf-8'); }
	function db_charset() { return config('db_charset', 'utf-8'); }

	function cs_f2i($str)
	{
		if($this->files_charset() == $this->internal_charset())
			return $str;
//		return  iconv($this->files_charset(), $this->internal_charset().'//IGNORE', $str) : $str;
		return  dc($str, $this->files_charset(), $this->internal_charset());
	}

	function cs_d2i($str) { return iconv($this->db_charset(), $this->internal_charset().'//IGNORE', $str); }

	function cs_i2d($str, $f='') { return iconv($this->internal_charset(), $this->db_charset().'//IGNORE', $str); }

	// Input to internal
	function cs_i2i($str) { return iconv($this->input_charset(), $this->internal_charset().'//IGNORE', $str); }

	function cs_i2o($str)
	{
		if(preg_match('/koi8|cp866/i', $out_cs = $this->output_charset()))
		{
			$str = str_replace(
				array('«'      ,'»',      '–',      '—'),
				array('&laquo;','&raquo;','&ndash;','&mdash;'),
				$str);
		}

		return iconv($this->internal_charset(), $out_cs.'//IGNORE', $str);
	}

	function cs_u2i($str) // utf-8 to internal
	{
		if(preg_match('/koi8|cp866/i', $ics = $this->internal_charset()))
		{
			$str = str_replace(
				array('«'      ,'»',      '–',      '—'),
				array('&laquo;','&raquo;','&ndash;','&mdash;'),
				$str);
		}

		return iconv('utf-8', $ics.'//IGNORE', $str);
	}

	function content($can_use_static = true, $recreate = false)
	{
		$use_static = config('cache_static') 
			&& ($recreate || ($can_use_static && $this->cache_static() > 0));

		$file = $this->static_file();
		$fe = file_exists($file);
		$fs = $fe && filesize($file) > 2000;

//		if(debug_is_balancer()) echo "cache_static=".config('cache_static').", can_use_static=$can_use_static, this->cache_static()={$this->cache_static()}, if($use_static && $file && $fe && !$recreate)".time();
		if($use_static && $file && $fe && !$recreate)
			return file_get_contents($this->static_file());

		if($use_static 
			&& !$fs 
			&& $this->use_temporary_static_file() 
			&& config('temporary_file_contents')
		)
			cache_static::save($this, /*$this->cs_i2o*/(str_replace(array(
				'$url',
				'$title',
				'$charset',
			), array(
				$this->url($this->page()),
				$this->title(),
				$this->output_charset(),
			), $this->cs_u2i(config('temporary_file_contents')))), 120);

		$content = $this->direct_content();

		if($this->internal_charset() != $this->output_charset())
			$content = $this->cs_i2o($content);

		if(empty($content))
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

	function link_time1() { return NULL; }
	function link_time2() { return NULL; }

	function __toString() { return $this->class_name().'://'.$this->id().($this->page() > 1 ? ','.$this->page() : ''); }

	function _relations() { return array(); }

	function print_properties() { print_d($this->data); }
}
