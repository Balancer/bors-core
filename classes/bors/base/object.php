<?php

class base_object extends base_empty
{
	var $data = array();

	function attr_preset() { return array(
	); }

	function properties_preset() { return array(
			'config_class' => config('config_class'),
			'access_engine' => '',
			'title' => $this->class_name(),	// В качестве заголовка объекта по умолчанию используется имя класса
			'url_engine' => 'url_calling2',
	); }

	var $___loaded = false;
	function loaded() { return $this->___loaded; }
	function set_loaded($value = true) { return $this->___loaded = $value; }

	private $__match;
	function set_match($match) { return $this->__match = $match; }

	function parents($exact = false)
	{
		if($ps = $this->get_data('parents'))
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
		if($image = object_property($this, 'image'))
 			$image_html = "<p>".$image->thumbnail('300x300')->html_code() . "</p>\n";
		else
			$image_html = '';

		if(($body = $this->description()))
			return $image_html . '<p>'.lcml_bbh($body).'</p>';

		if(($body = $this->source()))
			return $image_html . '<p>'.lcml_bbh($body).'</p>';

		return $image_html . '<p>'.$this->body().'</p>';
	}

	function rss_title() { return $this->title(); }
	function rss_url() { return $this->url(); }

	function has_smart_field($test_property)
	{
		$r_db = NULL;
		$r_table = NULL;
		$r_id_field = NULL;
		$r_db_field = NULL;

		foreach($this->fields_map_db() as $db => $tables)
		{
			foreach($tables as $table => $fields)
			{
				if(preg_match('!^(\w+)\((\w+)\)$!', $table, $m))
				{
					$table = $m[1];
					$r_id_field = $m[2];
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
		foreach($this->properties_preset() as $name => $val)
			if(!property_exists($this, $name) && !property_exists($this, "{$name}_ec"))
				$this->$name = $val;

		foreach($this->attr_preset() as $attr => $val)
			if(!array_key_exists($attr, $this->attr))
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

		if(@$this->__loaded)
			return true;

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

	function class_id()
	{
		if($this->__havefc())
			return $this->__lastc();

		return $this->__setc(class_name_to_id($this));
	}

	function class_title()    { return ec('Объект ').@get_class($this); }	// Именительный: Кто? Что?
	function class_title_rp() { return ec('объекта ').get_class($this); }	// РодительныйГенитив Кого? Чего?
	function class_title_dp() { return ec('объекту ').get_class($this); }	// Дательный Кому? Чему?
	function class_title_vp() { return ec('объект ').get_class($this); }	// Винительный Кого? Что?
	function class_title_tp() { return ec('объектом ').get_class($this); }	// Творительный Кем? Чем?
	function class_title_pp() { return ec('объекте ').get_class($this); }	// Предложный О ком? О чём?

	static function add_template_data($var_name, $value) { return $GLOBALS['cms']['templates']['data'][$var_name] = $value; }

	//TODO: под рефакторинг. Данные шаблона - отдельная сущность.
	function template_data($var_name) { return @$GLOBALS['cms']['templates']['data'][$var_name]; }

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

	function strict_auto_fields_check() { return config('strict_auto_fields_check', true); }
	function __call($method, $params)
	{
		// Это был вызов $obj->set_XXX($value, $db_up)
		if(preg_match('!^set_(\w+)$!', $method, $match))
			return $this->set($match[1], $params[0], $params[1]);

		// Проверяем нет ли уже загруженного значения данных объекта
		if(@array_key_exists($method, $this->data))
			return $this->data[$method];

		// Проверяем нет ли уже загруженного значения атрибута (временных несохраняемых данных) объекта
		if(@array_key_exists($method, $this->attr))
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

		$name = $method;

		// Проверяем одноимённые переменные (var $title = 'Files')
		if(property_exists($this, $name))
			return $this->set_attr($name, $this->$name);

		// Проверяем одноимённые переменные, требующие перекодирования (var $title_ec = 'Сообщения')
		$name_ec = "{$name}_ec";
		if(property_exists($this, $name_ec))
			return $this->set_attr($name, ec($this->$name_ec));

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
		if($db_update && @$this->data[$field] != $value) // TODO: если без контроля типов, то !=, иначе - !==
		{
			if(config('mutex_lock_enable'))
				$this->__mutex_lock();

			//TODO: продумать систему контроля типов.
			//FIXME: чёрт, тут нельзя вызывать всяких user, пока в них лезут ошибки типов. Исправить и проверить все основные проекты.
//			if(@$this->data[$field] == $value && @$this->data[$field] !== NULL && $value !== NULL)
//				debug_hidden_log('types', 'type_mismatch: value='.$value.'; original type: '.gettype(@$this->data[$field]).'; new type: '.gettype($value));

			$this->changed_fields[$field] = true;
			bors()->add_changed_object($this);
		}

		return $this->data[$field] = $value;
	}

	function have_data() { return !empty($this->data); }
	function has_changed() { return !empty($this->changed_fields); }

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

	/** Истинный заголовок объекта. Метод или параметр объекта. */
	function title_true() { return method_exists($this, 'title') ? $this->title() : @$this->data['title']; }

	function set_title($new_title, $db_update) { return $this->set('title', $new_title, $db_update); }

	function debug_title() { return "'{$this->title()}' {$this->class_name()}({$this->id()})"; }
	function debug_titled_link() { return "<a href=\"{$this->url()}\">'{$this->title()}' {$this->class_name()}({$this->id()})</a>"; }
	function debug_title_dc() { return dc("'{$this->title()}' {$this->class_name()}({$this->id()})"); }

	function description() { return @$this->data['description']; }
	function set_description($description, $db_update) { return $this->set('description', $description, $db_update); }

	function nav_name()
	{
		if(($nav_name = $this->get('nav_name', NULL, true)))
			return $nav_name;

		return  $this->get('nav_name_lower', config('nav_name_lower')) ? bors_lower($this->title()) : $this->title();
	}

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
			foreach($this->fields_map() as $property => $field)
			{
				if(is_numeric($property))
					$property = $field;

				$this->add_local_template_data($property, $this->$property());
			}
		}

		foreach($this->global_data() as $key => $value)
			$this->add_global_template_data($key, $value);

		if(($data = $this->local_data()))
		{
			if(!is_array($data)) //TODO: снести после отлавливания
				debug_hidden_log('__data_error', 'Not array local_data: '.print_r($data, true).' for '.$this->debug_title_dc());
			else
				foreach($data as $key => $value)
					$this->add_local_template_data($key, $value);
		}

		static $called = false; //TODO: в будущем снести вторые вызовы.
		if($called)
			return;

		$called = true;

		foreach($this->data_providers() as $key => $value)
			$this->add_template_data($key, $value);
	}

	function titled_url($append = '')
	{
		if(($target = $this->get('frame_target')))
			$target = " target=\"{$target}\"";
		else
			$target = '';

		if($append)
			$append = ' '.$append;

		return '<a href="'.$this->url($this->page())."\"{$target}{$append}>{$this->title()}</a>";
	}

	function titled_link($append = '') { return $this->titled_url($append); }

	function titled_target_link() { return $this->target()->titled_link(); }

	function titled_url_ex($title=NULL, $append=NULL, $url_append='')
	{
		if($title===NULL)
			$title = $this->title();

		return '<a href="'.$this->url($this->page()).$url_append.'"'.($append?' '.$append:'').">{$title}</a>"; 
	}

	function titled_link_ex($params)
	{
		$title = defval($params, 'title');
		if($title === NULL)
			$title = $this->title();

		$page = defval($params, 'page');
		if($page === NULL)
			$title = $this->page();

		return '<a href="'.$this->url_ex($page).defval($params, 'url_append').">{$title}</a>"; 
	}

	function nav_named_url() { return '<a href="'.$this->url($this->page())."\">{$this->nav_name()}</a>"; }
	function nav_named_link() { return '<a href="'.$this->url($this->page())."\">{$this->nav_name()}</a>"; }
	function titled_admin_url($title = NULL)
	{
		if($title === NULL)
		{
			$title = $this->title();
			if(!$title)
				$title = object_property($this->get('parent_object'), 'title');
			if(!$title)
				$title = '---';
		}
		return '<a href="'.$this->admin_url($this->page()).'">'.$title.'</a>';
	}

	function titled_edit_url($title = NULL)
	{
		if($title === NULL)
			$title = $this->title() ? $this->title() : '---';
		return '<a href="'.$this->admin_url($this->page()).'">'.$title.'</a>';
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
		return "<a href=\"{$this->admin_url($this->page())}\"><img src=\"/_bors/i/edit-16.png\" width=\"16\" height=\"16\" alt=\"edit\" title=\"$title\"/></a>";
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

		return "<a href=\"{$this->admin()->delete_url()}\"><img src=\"/_bors/images/drop-16.png\" width=\"16\" height=\"16\" alt=\"del\" title=\"$title\"/>{$text}</a>";
	}

	private function _setdefaultfor_url($target_id, $field_for_def)  { return "/admin/tools/set-default/?object={$this->internal_uri()}&target_id={$target_id}&target_field=$field_for_def"; }
	function imaged_set_default_link($target_id, $field_for_def, $title = NULL)
	{
		if($title === NULL)
			$title = ec('Сделать выбранным по умолчанию');

		return "<a href=\"".$this->_setdefaultfor_url($target_id, $field_for_def)."\"><img src=\"/_bors/i/set-default-16.gif\" width=\"16\" height=\"16\" alt=\"def\" title=\"$title\"/></a>";
	}

	function admin_engine() { return config('admin_engine', 'bors_admin_engine'); }
	function admin() { return object_load($this->admin_engine(), $this); }
	// Используется только при подключении BORS_EXT
	function tools() { return object_load('bors_object_tools', $this); }

	function admin_delete_link()
	{
		return $this->imaged_delete_url(NULL, 'Удалить '.bors_lower($this->class_title_vp()));
	}

	function form_errors() { return array(); }

	// true if break
	function check_data($data)
	{
		if(($conditions = $this->form_errors($data)))
		{
			if(($err = bors_form_errors($data, $conditions)))
				return go_ref_message($err);
		}
		else
		{
			foreach($this->check_value_conditions() as $key => $assert)
				if(!$this->check_value($key, defval($data, $key, $this->get($key)), $assert))
					return true;
		}

		return false;
	}

	function set_fields($array, $db_update_flag, $fields_list = NULL, $check_values = false)
	{
		//TODO: заюзать make_input_time? (funcs/datetime.php)
		if(!empty($array['time_vars']))
		{
//			print_d($array);
			foreach(explode(',', $array['time_vars']) as $var)
			{
				if(@$array["{$var}_month"] && @$array["{$var}_day"] && @$array["{$var}_year"])
				{
					$array[$var] = strtotime(intval(@$array["{$var}_year"])
						.'-'.intval(@$array["{$var}_month"])
						.'-'.intval(@$array["{$var}_day"])
						.' '.intval(@$array["{$var}_hour"])
						.':'.intval(@$array["{$var}_minute"])
						.':'.intval(@$array["{$var}_seconds"])); // .(@$array["{$var}_year"] >= 1970 ? ' +0300' : ' +0400')); //TODO: мегакостыль!
/*					echo intval(@$array["{$var}_year"])
						.'-'.intval(@$array["{$var}_month"])
						.'-'.intval(@$array["{$var}_day"])
						.' '.intval(@$array["{$var}_hour"])
						.':'.intval(@$array["{$var}_minute"])
						.':'.intval(@$array["{$var}_seconds"])."\n";
					echo $array[$var]."\n";
*/
//					echo date("r", $array[$var]);
					// mktime (@$array["{$var}_hour"], @$array["{$var}_minute"], @$array["{$var}_second"], @$array["{$var}_month"], @$array["{$var}_day"], @$array["{$var}_year"]);
				}
				else // Не полный формат даты, например, 2009-0-0 - пишем как строку.
					$array[$var] = intval(@$array["{$var}_year"]).'-'.intval(@$array["{$var}_month"]).'-'.intval(@$array["{$var}_day"]);

				if(empty($array["{$var}_month"]) && empty($array["{$var}_day"]) && empty($array["{$var}_year"]))
					$array[$var] = NULL;

				unset($array["{$var}_hour"], $array["{$var}_minute"], $array["{$var}_second"], $array["{$var}_month"], $array["{$var}_day"], $array["{$var}_year"]);
			}
//			exit();
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
//					echo "{$this->debug_title()}->{$method}($val, $db_update_flag);<br/>\n";
					$this->$method($val, $db_update_flag);
				}
			}
		}
//		exit();
		return true;
	}

	function check_value($field, $value, $assert=NULL)
	{
		if(!$assert)
		{
			$cond = $this->check_value_conditions();
			if(!($assert = @$cond[$field]))
				return true;
		}

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

	function save($cache_clean = true) { return $this->store($cache_clean); }

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

	function auto_objects() { return array(); }
	function auto_targets() { return array(); }

	function storage() { if($storage_class_name = $this->get('storage_engine')) return new $storage_class_name; else return NULL; }

	function access()
	{
		$access = $this->access_engine();
		if(!$access)
			$access = config('access_default', 'access_base');

		return object_load($access, $this);
	}

	function edit_url()
	{
		if($x = $this->get('edit_smart_object'))
			$obj = $x;
		else
			$obj = $this;

		return '/_bors/admin/edit-smart/?object='.$obj->internal_uri_ascii(); 
	}
//	function admin_url($exact = false) { return $exact ? NULL : '/_bors/admin/?object='.urlencode($this->internal_uri()); }
	function admin_url($exact = false) { return $exact ? NULL : $this->edit_url(); }
	function new_url()  { return '/_bors/admin/new-smart/?object='.urlencode($this->internal_uri()); }
	function admin_parent_url()
	{
		if(!preg_match('/'.preg_quote($this->admin_url(),'/').'/', bors()->main_object()->called_url()))
			return bors()->main_object()->called_url();

		if(($o = object_load($this->admin_url(true))))
			if($p = $o->parents())
				return $p[0];

		return @$_SERVER['HTTP_REFERER'];
	}

	function set_called_url($url) { return $this->attr['called_url'] = $url; }
	function called_url() { return @$this->attr['called_url']; }
	function called_url_no_get() { return preg_replace('/\?.+$/', '', @$this->attr['called_url']); }
	function _auto_redirect() { return true; }

	/**
	 * Возвращает ссылку на текущий объект для использования на сайте.
	 * @param  $page - опциональный параметр номера страницы при многостраничной разбивке объекта при выводе
	 * @return Строка со ссылкой
	 */
	function url($page = NULL)
	{
		if(empty($this->attr['_url_engine_object'])/* || !$this->_url_engine->id() ?? */)
			if(!($this->attr['_url_engine_object'] = object_load($this->get('url_engine'), $this)))
				debug_exit("Can't load url engine '{$this->get('url_engine')}' for class {$this}");

		return $this->attr['_url_engine_object']->url($page);
	}

	function url_ex($args)
	{
		if(!($url_engine = defval($args, 'url_engine')))
			$url_engine = $this->get('url_engine');

		return object_load($url_engine)->url(defval($args, 'page'));
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
			$this->_dbh = new driver_mysql($database_name ? $database_name : $this->get('db_name', config('main_bors_db')));

		return $this->_dbh;
	}

	//TODO: разобраться с сериализацией приватных данных
	public function __sleep()
	{
		if($this->_dbh)
		{
			$this->_dbh->close(); 
			$this->_dbh = NULL;
		}

		return parent::__sleep();
	}

	/** Вернуть карту полей объекта для главной таблицы (если их несколько) */
	function fields_map() { return $this->main_table_fields(); }

	function main_table_fields() { return array('id'); }

	function id_field()
	{
		if(@array_key_exists($this->attr, '__id_field_name'))
			return $object->attr['__id_field_name'];

		$ff = method_exists($this, 'table_fields') ? $this->table_fields() : $this->fields_map();
		return defval($ff, 'id', 'id');
	}

	function title_field() { return defval($this->fields_map(), 'title', 'title'); }

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

	var $___args = array();
	function set_args($args) { return $this->___args = $args; }
	function _set_arg($name, $value) { return $this->___args[$name] = $value; }
	function args($name=false, $def = NULL) { return $name ? (array_key_exists($name, $this->___args) ? $this->___args[$name] : $def) : $this->___args; }
	function arg($name, $def = NULL) { return array_key_exists($name, $this->___args) ? $this->___args[$name] : $def; }

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

		$this->set_visits(intval($this->visits()) + intval($inc), true);
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

	function extends_class_id()
	{
		if($this->__havefc())
			return $this->__lastc();

		return $this->__setc(class_name_to_id($this->extends_class()));
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

//	function __use_static() { return config('cache_static') && $this->cache_static() > 0; }

	function content($can_use_static = true, $recreate = false)
	{
		$use_static = config('cache_static') 
			&& ($recreate || ($can_use_static && $this->cache_static() > 0));

		$file = $this->static_file();
		$fe = file_exists($file);
		$fs = $fe && filesize($file) > 2000;

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

	function object_title() { return strip_tags(bors_lower($this->class_title()).ec(' «').$this->title().ec('»')); }
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

	function page() { return $this->attr('page'); }
	function set_page($page) { return $this->set_attr('page', $page ? $page : $this->default_page()); }

	function empty_id_handler() { return NULL; }
	function auto_assign_all_fields() { return false; }

	function link_time1() { return NULL; }
	function link_time2() { return NULL; }

	function __toString() { return $this->class_name().'://'.$this->id().($this->page() > 1 ? ','.$this->page() : ''); }

	function _relations() { return array(); }

	function print_properties() { print_d($this->data); }

	function __field_type($field_name)
	{
		$fields = $this->fields_map();
		$desc = @$fields[$field_name];

		if($type = @$desc['type'])
			return $type;

		if(preg_match('/_id$/', $field_name))
			return 'int';

		if(preg_match('/^is_/', $field_name))
			return 'bool';

		return 'string';
	}

	function __field_title($field_name)
	{
		$fields = $this->fields_map();
		$desc = @$fields[$field_name];

		return defval($desc, 'title', $field_name);
	}

	function add_keyword($keyword, $up)
	{
		$keyword = trim($keyword);
		$keywords = $this->keywords();
		foreach($keywords as $kw)
		{
			$kw = trim($kw);
			if(common_keyword::compare_eq($kw, $keyword))
				return;
		}

		$keywords[] = $keyword;
		$this->set_keywords($keywords, $up);
	}

	function remove_keyword($keyword, $up)
	{
		$keyword = trim($keyword);
		$keywords = array();
		foreach($this->keywords() as $kw)
		{
			$kw = trim($kw);
			if(!common_keyword::compare_eq($kw, $keyword))
				$keywords[] = $kw;
		}

		$this->set_keywords($keywords, $up);
	}

	function logger()
	{
		if($this->__havefc())
			return $this->__lastc();

		return $this->__setc(object_load(config('logs.default_logger_class', 'bors_log_stub'), $this));
	}
}
