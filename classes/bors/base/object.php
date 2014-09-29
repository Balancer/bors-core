<?php

class base_object extends bors_object_simple
{
	var $data = array();
	protected static $__auto_objects = array();

	function attr_preset() { return array(
		'title' => $this->class_title().' '.$this->class_name(),	// В качестве заголовка объекта по умолчанию используется имя класса
	); }

	function _url_engine_def() { return 'url_calling2'; }
	function _config_class_def() { return config('config_class'); }

//	При настройке проверить:
//	— http://www.aviaport.ru/services/events/arrangement/
// 	— http://admin.aviaport.ru/directory/aviafirms/31/
//	function _title_def() { return $this->class_title().' '.$this->class_name(); }

	function _access_engine_def() { return NULL; }

	function properties_preset() { return array(
	); }

	var $___loaded = false;
	function is_loaded() { return $this->___loaded; }
	function set_is_loaded($value) { return $this->___loaded = $value; }

	private $__match;
	function set_match($match) { return $this->__match = $match; }

	function parents()
	{
		if($ps = $this->get_data('parents'))
			return $ps;

		if(empty($this->__match[2]))
			$parent = secure_path(dirname($this->called_url()).'/');
		else
			$parent = "http://{$this->__match[1]}{$this->__match[2]}";

		return $this->data['parents'] = array($parent);
	}

	function parent_objects()
	{
		if($this->__havefc())
			return $this->__lastc();

		$parent_objects = array();
		foreach($this->parents() as $p)
		{
			if(is_object($p))
			{
				if(empty($parent_objects[$p->internal_uri_ascii()]))
					$parent_object[$p->internal_uri_ascii()] = $p;
			}
			else
			{
				if($p = bors_load_uri(trim($p)))
					if(empty($parent_objects[$p->internal_uri_ascii()]))
						$parent_object[$p->internal_uri_ascii()] = $p;
			}
		}

		return $this->__setc($parent_object);
	}

	function child_objects()
	{
		if($this->__havefc())
			return $this->__lastc();

		$child_objects = array();
		foreach($this->children() as $c)
		{
			if(is_object($c))
			{
				if(empty($child_objects[$c->internal_uri_ascii()]))
					if(!$c->get('is_deleted'))
						$child_objects[$c->internal_uri_ascii()] = $c;
			}
			else
			{
				if($c = bors_load_uri(trim($c)))
					if(empty($child_objects[$c->internal_uri_ascii()]))
						if(!$c->get('is_deleted'))
							$child_objects[$c->internal_uri_ascii()] = $c;
			}
		}

		return $this->__setc($child_objects);
	}

	function rss_body($object, $strip = 0)
	{
		// Этот config пока используется только на лентах топиков:
		// http://www.wrk.ru/society/2014/08/topic-89787-rss.xml
		// Подумать, как сделать красиво и локально
		if(!config('rss_skip_images') && ($image = object_property($this, 'image')))
 			$image_html = "<p>".$image->thumbnail('300x300')->html_code() . "</p>\n";
		else
			$image_html = '';

		if(($body = $this->description()))
			return $image_html . '<p>'.bors_lcml::bbh($body).'</p>';

		if(($body = $this->source()))
			return $image_html . '<p>'.bors_lcml::bbh($body).'</p>';

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

		foreach(bors_lib_orm::all_fields($this) as $field)
		{
			if($field['property'] == $test_property)
			{
				$r_db_field = $field['name'];
				$r_db = $field['db'];
				$r_table = $field['table'];
			}

			if($field['property'] == 'id')
				$r_id_field = $field['name'];

			if($r_id_field && $r_db_field)
				return array($r_db, $r_table, $r_id_field, $r_db_field);
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
			if(!array_key_exists($attr, $this->defaults))
				$this->defaults[$attr] = $val;

		if(($config = $this->config_class()))
		{
			$this->config = new $config($this);

			if(!$this->config)
				debug_exit("Can't load config class '{$config}'.");

			$this->config->target_configure();
		}
	}

	function data_load()
	{
		if(!empty($this->__loaded))
			return true;

		$this->__loaded = false;

		if(($storage_engine = $this->storage_engine()))
		{
			$storage_engine = object_load($storage_engine, NULL, array('no_load_cache' => true));
			if(!$storage_engine)
				bors_throw("Can't load storage engine '{$this->storage_engine()}' for class <b>{$this->class_name()}</b><br/>at {$this->class_file()}  in dirs:<br/>".join(",<br/>\n", bors_dirs()));
			elseif($storage_engine->load($this) !== false || $this->can_be_empty())
				$this->__loaded = true;
		}

		if(!$this->config && ($config = $this->config_class()))
		{
			$this->config = new $config($this);
			if(!$this->config)
				debug_exit("Can't load config ".$this->config_class());

			$this->config->target_configure();
		}

		if(($data_provider = $this->data_provider()))
			object_load($data_provider, $this)->fill();

		return $this->__loaded;
	}

	function class_id()
	{
		if($this->__havefc())
			return $this->__lastc();

		return $this->__setc(class_name_to_id($this));
	}

	function _class_title_def()    { return ec('Объект ').@get_class($this); }	// Именительный: Кто? Что?
	function _class_title_rp_def() { return bors_object_titles::class_title_gen($this); }	// Родительный/Генитив Кого? Чего?
	function _class_title_dp_def() { return bors_object_titles::class_title_dat($this); }	// Дательный Кому? Чему?

	function _class_title_vp_def() { return bors_object_titles::class_title_acc($this); }	// Accusativ, Винительный Кого? Что?
	function _class_title_tp_def() { return bors_object_titles::class_title_abl($this); }	// Творительный Кем? Чем?
	function _class_title_pp_def() { return bors_object_titles::class_title_pre($this); }	// Предложный О ком? О чём?

	function _class_title_m_def()   { return bors_object_titles::class_title_plur($this); }	// Множественный именительный
	function _class_title_rpm_def() { return bors_object_titles::class_title_gen_plur($this); }	// Множественный родительный
	function _class_title_dpm_def() { return bors_object_titles::class_title_dat_plur($this); }	// Множественный дательный, Кому? Чему?
	function _class_title_tpm_def() { return bors_object_titles::class_title_abl_plur($this); }	// Множественный Творительный Кем? Чем?
//	function class_title_tpm() { return ec('объектами ').@get_class($this); }	// Множественный Творительный Кем? Чем?

	// Множественный (Plural) дательный (Genitive): Архив чего? — объектов.
	function _class_title_pg_def() { return bors_object_titles::class_title_gen_plur($this); }

	static function add_template_data($var_name, $value) { return $GLOBALS['cms']['templates']['data'][$var_name] = $value; }

	//TODO: под рефакторинг. Данные шаблона - отдельная сущность.
	static function template_data($var_name) { return empty($GLOBALS['cms']['templates']['data'][$var_name]) ? NULL : $GLOBALS['cms']['templates']['data'][$var_name]; }

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
			return $this->set($match[1], $params[0], array_key_exists(1, $params) ? $params[1] : true);

		// Проверяем нет ли уже загруженного значения атрибута (временных несохраняемых данных) объекта
		// Приоритет атрибута выше, чем приоритет параметров, так как в атрибутах
		// может лежать изменённое значение параметра
		// Если это где-то что-то поломает — исправить там, а не тут.
		if(@array_key_exists($method, $this->attr))
		{
			// Если хранимый атрибут — функция (и в случае строки её имя 3 символа и более), то вызываем её, передав параметр.
			if(is_callable($this->attr[$method]) && (!is_string($this->attr[$method]) || strlen($this->attr[$method]) >= 3))
				return call_user_func_array($this->attr[$method], $params);

			// Иначе — просто возвращаем значение.
			return $this->attr[$method];
		}

		// Проверяем нет ли уже загруженного значения данных объекта
		if(@array_key_exists($method, $this->data))
			return $this->data[$method];

		// Проверяем нет ли уже загруженного значения автообъекта
		if(array_key_exists($method, self::$__auto_objects))
		{
			$x = self::$__auto_objects[$method];
			if($x['property_value'] == $this->get($x['property']))
				return $x['value'];
		}

//		echo $this->debug_title();
		// Проверяем автоматические объекты.
		// Избегаем зацикливания. Если уже внутри проверки, то ещё раз не проверяем.
		if(empty($this->attr['___in_auto_object_routine']))
		{
			$this->attr['___in_auto_object_routine'] = true;
			$auto_objs = $this->auto_objects();
			unset($this->attr['___in_auto_object_routine']);
		}

		if(!empty($auto_objs[$method]))
		{
			if(preg_match('/^(\w+)\((\w+)\)$/', $auto_objs[$method], $m))
			{
				$property = $m[2];
				if(config('orm.auto.cache_attr_skip'))
					return bors_load($m[1], $this->get($property));
				else
				{
					$property_value = $this->get($property);
					$value = bors_load($m[1], $property_value);
					self::$__auto_objects[$method] = compact('property', 'property_value', 'value');
					return $value;
				}
			}
		}

		// Автоматические целевые объекты (имя класса задаётся)
		$auto_targs = $this->auto_targets();
		if(!empty($auto_targs[$method]))
			if(preg_match('/^(\w+)\((\w+)\)$/', $auto_targs[$method], $m))
				if(config('orm.auto.cache_attr_skip'))
					return object_load($this->$m[1](), $this->$m[2]());
				else
					return $this->attr[$method] = object_load($this->$m[1](), $this->$m[2]());

		$name = $method;

		// Проверяем одноимённые переменные (var $title = 'Files')
		if(property_exists($this, $name))
			return $this->set_attr($name, $this->$name);

		// Проверяем одноимённые переменные, требующие перекодирования (var $title_ec = 'Сообщения')
		$name_ec = "{$name}_ec";
		if(property_exists($this, $name_ec))
			return $this->set_attr($name, ec($this->$name_ec));

		// Ищем методы, перекрываемые переменным по умолчанию
		$m = "_{$name}_def";
		if(method_exists($this, $m))
		{
			// Try убран, так как нужно решить, как обрабатывать всякие function _title_def() { bors_throw('Заголовок не указан!';} — см. bors_rss
			$value = call_user_func(array($this, $m));
//			var_dump($m, $value);
//			try { $value = $this->$m(); }
//			catch(Exception $e) { $value = NULL; }
			return $this->attr[$name] = $value;
		}

		// Проверяем нет ли значения по умолчанию — это вместо бывшего attr
		if(@array_key_exists($method, $this->defaults))
			return $this->defaults[$method];

		if($this->strict_auto_fields_check())
		{
			$trace = debug_backtrace();
			$trace = array_shift($trace);
			bors_throw("__call[".__LINE__."]:
undefined method '$method' for class '<b>".get_class($this)."({$this->id()})</b>'<br/>
defined at {$this->class_file()}<br/>
class_filemtime=".date('r', $this->class_filemtime())."<br/>
". (!empty($trace['file']) ? "called from {$trace['file']}:{$trace['line']}" : ''));
		}

		return NULL;
	}

	function pre_parse() { return false; }

	function pre_show()
	{
		if(config('objects_visits_counting'))
			bors_objects_visit::inc($this);

		return false;
	}

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

	function set($prop, $value, $db_update=true)
	{
		// Строго проверяем, наш ли это метод. Или присоединённого объекта. Или — ошибка
/*
		if(!array_key_exists($prop, $this->data))
		{
			// Нужно придумать контроль отсутствия ключа.
			foreach($this->_prop_joins as $x)
				$x->set($prop, $value, $db_update);

			// У атрибутов выше приоритет. Так что их тоже надо менять. Ну а данные — они на запись.
			return $this->attr[$prop] = $value;
		}
*/
		if($db_update
				&& !is_array($value)
				&& !is_object($value)
				&& strcmp(empty($this->data[$prop]) ? NULL : $this->data[$prop], $value)
			) // TODO: если без контроля типов, то !=, иначе - !==
		{
			if(config('mutex_lock_enable'))
				$this->__mutex_lock();

//			if(config('is_developer')) echo debug_trace();

			//TODO: продумать систему контроля типов.
			//FIXME: чёрт, тут нельзя вызывать всяких user, пока в них лезут ошибки типов. Исправить и проверить все основные проекты.
//			if(@$this->data[$prop] == $value && @$this->data[$prop] !== NULL && $value !== NULL)
//				debug_hidden_log('types', 'type_mismatch: value='.$value.'; original type: '.gettype(@$this->data[$prop]).'; new type: '.gettype($value));

			// Запоминаем первоначальное значение переменной.
			if(empty($this->changed_fields) || !array_key_exists($prop, $this->changed_fields))
				$this->changed_fields[$prop] = empty($this->data[$prop]) ? NULL : $this->data[$prop];

			bors()->add_changed_object($this);
		}

		$this->attr[$prop] = $value; // У атрибутов выше приоритет. Так что их тоже надо менять. Ну а данные — они на запись.
		return $this->data[$prop] = $value;
	}

	function set_def($property, $default, $db_update=true)
	{
		if($prev = $this->get($property))
			return $prev;

		return $this->set($property, $default, $db_update);
	}

	function have_data() { return !empty($this->data); }
	function has_changed()
	{
		if(empty($this->changed_fields))
			return false;

		foreach($this->changed_fields as $property => $old_value)
		{
			if($property == 'modify_time')
				continue;

			if(!is_null($old_value) && strcmp($old_value, $this->get($property)))
				return true;
		}

		return false;
	}

	function render_engine() { return config('render_engine', false); }

	function is_cache_disabled() { return true; }
	function template_vars() { return 'body source me me_id'; }
	function template_local_vars() { return 'create_time description id modify_time nav_name title'; }

	function set_create_time($unix_time, $db_update = true) { return $this->set('create_time', intval($unix_time), $db_update); }
	function create_time()
	{
		if(!empty($this->data['create_time']))
			return $this->data['create_time'];

		if(!empty($this->data['modify_time']))
			return $this->data['modify_time'];

		return NULL;
	}

	function set_modify_time($unix_time, $db_update = true) { return $this->set('modify_time', $unix_time, $db_update); }
	function modify_time()
	{
		if(!empty($this->data['modify_time']))
			return $this->data['modify_time'];

		return NULL;
	}

	/** Истинный заголовок объекта. Метод или параметр объекта. */
	function title_true() { return method_exists($this, 'title') ? $this->title() : empty($this->data['title']) ? NULL : $this->data['title']; }

	function set_title($new_title, $db_up=true) { return $this->set('title', $new_title, $db_up); }

	function debug_title() { return "'".trim(object_property($this, 'title'))."' {$this->class_name()}({$this->id()})"; }
	function debug_titled_link() { return "<a href=\"{$this->url()}\">'{$this->title()}' {$this->class_name()}({$this->id()})</a>"; }
	function debug_title_dc() { return dc("'{$this->title()}' {$this->class_name()}({$this->id()})"); }

	function debug_title_short() { return "{$this->class_name()}({$this->id()})"; }

	function _description_def() { return empty($this->data['description']) ? NULL : $this->data['description']; }
	function set_description($description, $db_update=true) { return $this->set('description', $description, $db_update); }

	function _nav_name_def()
	{
		if(($nav_name = $this->get('nav_name', NULL, true)))
			return $nav_name;

		return $this->get('nav_name_lower', config('nav_name_lower')) ? bors_lower($this->title()) : $this->title();
	}

	function _nav_name_true_def() { return $this->get('nav_name', NULL, true); }

	function set_nav_name($nav_name, $db_update) { return $this->set('nav_name', $nav_name, $db_update); }

	function _template_def() { return defval($this->data, 'template', defval($this->attr, 'template', config('default_template'))); }
	function set_template($template, $db_update = true) { return $this->set('template', $template, $db_update); }

	function parents_string() { return join("\n", $this->parents());  }
	function set_parents_string($string, $dbup)
	{
		$this->set_parents($x = array_filter(explode("\n", $string)), $dbup);
		$this->set('parents_string', join("\n", $x), $dbup);
		return $string;
	}

	function children() { return array();  }
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

	function titled_link()
	{
		$url = $this->url_ex($this->page());
		$title = $this->get('title');

		if(!$title)
			$title = '???';

		return "<a href=\"{$url}\">{$title}</a>";
	}

	function url_in_container() { return $this->url(); }
	function url_for_igo() { return $this->url_in_container(); }
	function title_in_container() { return $this->title(); }

	function titled_link_in_container()
	{
		return '<a href="'.$this->url_in_container()."\">{$this->title_in_container()}</a>";
	}

	function titled_target_link() { return $this->target()->titled_link(); }

	function titled_url_ex($title=NULL, $append=NULL, $url_append='')
	{
		if($title===NULL)
			$title = $this->title();

		return '<a href="'.$this->url_ex($this->page()).$url_append.'"'.($append?' '.$append:'').">{$title}</a>"; 
	}

	function titled_link_ex($params = array())
	{
		// Если параметр не массив, то это номер страницы.
		if(!is_array($params))
			$params = array('page' => $params);

		$title = defval($params, 'title');
		if($title === NULL)
			$title = $this->title();

		if(!$title)
			$title = '???';

		if($popup = defval($params, 'popup'))
			$popup = " title=\"".htmlspecialchars($popup)."\"";

		if($class = defval($params, 'class'))
			$class = " class=\"".htmlspecialchars($class)."\"";

		if($class = defval($params, 'css'))
			$class = " class=\"".htmlspecialchars($class)."\"";

		if($style = defval($params, 'style'))
			$style = " style=\"".htmlspecialchars($style)."\"";

		if($target = defval($params, 'target'))
			$target = " target=\"".htmlspecialchars($target)."\"";
		else
			$target = "";

		$page = defval($params, 'page');
//		if($page === NULL) //TODO: WTF? Изучить все вызовы.
//			$title = $this->page();

		if(!($url = defval($params, 'url')))
			$url = $this->url_ex($page);

		$url = str_replace('%OBJECT_ID%', $this->id(), $url);

		return '<a href="'.$url.defval($params, 'url_append')."\"{$popup}{$target}{$class}{$style}>{$title}</a>"; 
	}
	function titled_link_target($target) { return $this->titled_link_ex(array('target' => $target)); }

	function nav_named_url() { return '<a href="'.$this->url_ex($this->page())."\">{$this->nav_name()}</a>"; }
	function nav_named_link($append = NULL) { return '<a href="'.$this->url_ex($this->page())."\"".($append?' '.$append:'').">{$this->nav_name()}</a>"; }
	function nav_named_link_ex($params = array()) { set_def($params, 'title', $this->get('nav_name')); return $this->titled_link_ex($params); }
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
		return '<a rel="nofollow" href="'.$this->admin_url($this->page()).'">'.$title.'</a>';
	}

	function titled_edit_url($title = NULL)
	{
		if($title === NULL)
			$title = $this->title() ? $this->title() : '---';

		return '<a rel="nofollow" href="'.$this->admin_url($this->page()).'">'.$title.'</a>';
	}

	function imaged_admin_link($title = NULL)
	{
		if($title === NULL)
			$title = ec('Администрировать ').bors_lower($this->class_title_rp());
		return "<a rel=\"nofollow\" href=\"{$this->admin_url($this->page())}\"><img src=\"/_bors/images/edit-16.png\" width=\"16\" height=\"16\" alt=\"edit\" title=\"$title\"/></a>";
	}

	function imaged_edit_link($title = NULL) { return $this->imaged_edit_url($title); }
	function imaged_edit_url($title = NULL)
	{
		if($title === NULL)
			$title = ec('Редактировать ').bors_lower($this->class_title_rp());

		return "<a rel=\"nofollow\" href=\"{$this->edit_url($this->page())}\"><img src=\"/_bors/i/edit-16.png\" width=\"16\" height=\"16\" alt=\"edit\" title=\"$title\"/></a>";
	}

	function imaged_texted_edit_link($text) { return $this->imaged_edit_link_ex(array('text' => $text)); }

	function imaged_edit_link_ex($params = array())
	{
		$title = popval($params, 'title');
		if($title === NULL)
			$title = ec('Редактировать ').bors_lower($this->class_title_rp());

		$text = popval($params, 'text');
		if($text)
			$text = "&nbsp;{$text}";

		return "<a rel=\"nofollow\" href=\"{$this->edit_url($this->page())}\"><img src=\"/_bors/i/edit-16.png\" width=\"16\" height=\"16\" alt=\"edit\" title=\"$title\"/>{$text}</a>";
	}

	function titled_new_link($title = NULL)
	{
		if($title === NULL)
			$title = $this->title() ? $this->title() : '---';
		return '<a rel="nofollow" href="'.$this->new_url($this->page()).'">'.$title.'</a>';
	}

	function imaged_delete_link($text = NULL, $title = NULL) { return $this->imaged_delete_url($title, $text); }

	function imaged_delete_url($title = NULL, $text = NULL)
	{
		if($title == 'del' || !$title)
			$title = ec('Удалить ').bors_lower($this->class_title_vp());

		if($text === NULL)
			$text = $title;

		if($text)
			$text = '&nbsp;'.$text;

		return "<a rel=\"nofollow\" href=\"{$this->admin()->delete_url()}\"><img src=\"/_bors/images/drop-16.png\" width=\"16\" height=\"16\" alt=\"del\" title=\"$title\"/>{$text}</a>";
	}

	private function _setdefaultfor_url($target_id, $field_for_def)  { return "/admin/tools/set-default/?object={$this->internal_uri()}&target_id={$target_id}&target_field=$field_for_def"; }
	function imaged_set_default_link($target_id, $field_for_def, $title = NULL)
	{
		if($title === NULL)
			$title = ec('Сделать выбранным по умолчанию');

		return "<a rel=\"nofollow\" href=\"".$this->_setdefaultfor_url($target_id, $field_for_def)."\"><img src=\"/_bors/i/set-default-16.gif\" width=\"16\" height=\"16\" alt=\"def\" title=\"$title\"/></a>";
	}

	function admin_engine() { return config('admin_engine', 'bors_admin_engine'); }
	function admin() { return bors_load($this->admin_engine(), $this); }
	// Используется только при подключении BORS_EXT
	function tools() { return bors_load('bors_object_tools', $this); }
	function urls($type = NULL)
	{
		$helper = bors_load('bors_object_urls',  $this);
		if(!$type)
			return $helper;

		return $helper->urls($type);
	}

	function admin_delete_link()
	{
		return $this->imaged_delete_url(NULL, 'Удалить '.bors_lower($this->class_title_vp()));
	}

	function form_errors($data) { return array(); }

	// true if break
	function check_data(&$data)
	{
		if(($conditions = $this->form_errors($data)))
		{
			if(($err = bors_form_errors($data, $conditions)))
				return go_ref_message($err);
		}
		else
		{
			foreach($this->check_value_conditions() as $key => $assert)
			{
				if(is_numeric($key) && preg_match('/^\w+$/', $assert))
				{
					$key = $assert;
					$assert = "!=''|Параметр должен быть указан";
				}

				if(!$this->check_value($key, defval($data, $key, $this->get($key)), $assert))
					return true;
			}
		}

		foreach(bors_lib_orm::all_fields($this) as $f)
			if(!empty($f['is_req']) && empty($data[$f['property']]))
				return go_ref_message(ec('Не задано обязательное поле «').$f['title'].ec('»'), array('error_fields' => $f['property']));

		return false;
	}

	function set_fields($array, $db_update_flag=true, $fields_list = NULL, $check_values = false)
	{
		if(!empty($array['time_vars']))
			bors_lib_time::parse_form($array);

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

		$res = NULL;
		eval("\$res = ('".addslashes($value)."' $assert);");
		if(empty($res))
		{
			bors_message($message);
			return false;
		}

		return true;
	}

	function check_value_conditions() { return array(); }

	function save() { return $this->store(); }
	function store()
	{
		if($this->attr('__store_entered'))
			return;

		$this->set_attr('__store_entered', true);

		if(!$this->id())
			return $this->set_attr('__store_entered', false);

		if(empty($this->changed_fields))
			return $this->set_attr('__store_entered', false);

		if($this->get('_read_only'))
			return $this->set_attr('__store_entered', false);

		if(method_exists($this, 'skip_save') && $this->skip_save()) //TODO: костыль для bors_admin_image_append
			return $this->set_attr('__store_entered', false);

		if(!($storage = $this->storage()))
		{
			debug_hidden_log('storage_error', 'Not defined storage engine for '.$this->class_name());
			return $this->set_attr('__store_entered', false);
		}

		$storage->save($this);

		bors_objects_helper::update_cached($this);

		//TODO: Фактически хардкод. Надо придумать метод сигнализации об изменении объекта. Типа, ->set_was_modified()
		$was_modified = array_key_exists('modify_time', $this->changed_fields);

//		if($was_modified)
//			debug_hidden_log('00-modified', $this->debug_title().print_r($this->changed_fields, true));

		if($this->get('is_changes_logging'))
			bors_objects_changelog::add($this);

		$this->changed_fields = array();

		if(config('debug_trace_changed_save'))
			echo 'Save '.$this->debug_title()."\n";

		$this->__update_relations();
		save_cached_object($this);

		if(config('search_autoindex') && $this->auto_search_index())
		{
			include_once('engines/search.php');

			if(config('bors_tasks'))
				bors_tools_tasks::add_task($this, 'bors_task_index', 0, -10);
			else
				bors_search_object_index($this, 'replace');
		}

		if($was_modified)
			$this->cache_clean();

		bors()->drop_changed_object($this->internal_uri());
		$this->set_attr('__store_entered', false);
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

	function cache_static() { return 0; }
	function cache_static_expire() { return $this->cache_static() ? $this->cache_static() + time() : 0; }

	// Признак постоянного существования объекта.
	// Если истина, то объект создаётся не по первому запросу, а при сохранении
	// параметров и/или сбросе кеша, удалении старого статического кеша и т.п.
	// Применимо только при cache_static === true
	function cache_static_recreate() { return false; }
	function cache_static_can_be_dropped() { return true; }

	function cache_groups() { return ''; }
	function cache_depends_on() { return $this->cache_groups(); }

	function cache_groups_parent() { return ''; }
	function cache_provides() { return $this->cache_groups_parent(); }

	function uid() { return md5($this->class_id().'://'.$this->id().','.$this->page()); }
	function can_cached() { return true; }

	function cache_children() { return array(); }
	function cache_parents() { return array(); }

	// Чистка не только нашего объекта, но и зависящих от него.
	// Поэтому нужно поосторожнее с ограничениями функции.
	// Мы меняем topic, но чистить нужно topics_view
	function cache_clean()
	{
		if($this->attr('__cache_clean_entered'))
			return;

		$this->set_attr('__cache_clean_entered', true);

		// Сперва чистим группы. Так как cache_clean_self() генерирует статические файлы и обновляет группы
		// Если группы чистить потом, то они удалятся и не восстановятся.
		if(config('cache_database'))
		{
			foreach(explode(' ', $this->cache_provides()) as $group_name)
				if($group_name)
					foreach(bors_find_all('cache_group', array('cache_group' => $group_name)) as $group)
						if($group)
							$group->clean();

			// Чистим все прямые привязки других объектов на событие изменения нашего.
			// По факту сейчас не используется. Если где-то всплывёт надобность, то
			// надо будет ввести признак возможности внешней привязки к объекту, чтобы
			// не дёргать этот find на каждой модификации мелких объектов, типа topic_visits
//			foreach(bors_find_all('cache_group', array('cache_group' => $this->internal_uri_ascii())) as $group)
//				if($group)
//					$group->clean();
		}

//		echo "<b>cache_clean</b> {$this->debug_title()}: ".print_r($this->changed_fields, true)."<br/>\n";
		$this->cache_clean_register();

		foreach($this->cache_children() as $child_cache)
			if($child_cache && !$child_cache->was_cleaned())
				$child_cache->cache_clean_register();

		foreach($GLOBALS['bors_cache_clean_queue'] as $uri => $object)
		{
			$object->cache_clean_self();
			unset($GLOBALS['bors_cache_clean_queue'][$uri]);
		}

		$this->set_attr('__cache_clean_self_entered', false);
	}

	function cache_clean_self()
	{
		if($this->was_cleaned())
			return;

		$this->set_was_cleaned(true);

		if($this->cache_static() > 0 && $this->cache_static_can_be_dropped())
			cache_static::drop($this);

		// Чистка memcache и Cache.
		save_cached_object($this);
	}

	// Поставить объект $this в очередь на очистку
	function cache_clean_register()
	{
		$GLOBALS['bors_cache_clean_queue'][$this->internal_uri_ascii()] = $this;
	}

	/**
		Если выставлен этот флаг, то новые объекты в БД будут
		добавляться по методу replace, замещая возможное старое значение
	*/
	function replace_on_new_instance() { return false; }
	/**
		Если выставлен этот флаг, то новые объекты в БД будут
		добавляться по методу insert ignore, не трогая возможное старое значение

		В случае отсутствия определения replace или ignore, вставка идёт
		с помощью обычного insert и образует ошибку в случае дублей
	*/
	function ignore_on_new_instance()  { return false; }

	function data_provider() { return NULL; }
	function data_providers() { return array(); }

	function auto_objects() { return array(); }

	function auto_targets()
	{
		return array(
			'referent' => 'referent_class(id)',
		);
	}

	function storage()
	{
		if($storage_class_name = $this->get('storage_engine', config('storage.default.class_name')))
			return new $storage_class_name($this);
		else
			return NULL;
	}

	function access()
	{
		$access = $this->access_engine();
		if(!$access)
			$access = config('access_default');
//			bors_throw(ec('Не задан режим доступа к ').$this->object_titled_dp_link());

		return bors_load($access, $this);
	}

	function edit_url()
	{
		if($x = $this->get('edit_smart_object'))
			$obj = $x;
		else
			$obj = $this;

		return '/_bors/admin/edit-smart/?object='.$obj->internal_uri_ascii(); 
	}

	function _admin_url_def() { return $this->edit_url(); }
	function new_url()  { return '/_bors/admin/new-smart/?object='.urlencode($this->internal_uri()); }
	function admin_parent_url()
	{
		$called_url = object_property(bors()->main_object(), 'called_url');
		if($called_url && !preg_match('/'.preg_quote($this->admin_url(),'/').'/', $called_url))
			return bors()->main_object()->called_url();

		if(($o = object_load($this->admin_url())))
		{
			if(!$o->attr('___parent_searching'))
			{
				$o->set_attr('___parent_searching', true);
				if($p = $o->parents())
					return $p[0];
			}
		}

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
	function url() { return $this->url_ex($this->page()); }

	function url_ex($args)
	{
		if(is_object($args))
			$page = popval($args, 'page');
		else
		{
			$page = $args;
			$args = array();
		}

		if(!($url_engine = defval($args, 'url_engine')))
			$url_engine = $this->get('url_engine');

		$key = '_url_engine_object_'.$url_engine;

		if(empty($this->attr[$key])/* || !$this->_url_engine->id() ?? */)
			if(!($this->attr[$key] = bors_load($url_engine, $this)))
				bors_throw("Can't load url engine '{$url_engine}' for class {$this->class_name()}");

		return $this->attr[$key]->url_ex($page);
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
	function fields_map() { return $this->table_fields(); }

	function table_fields() { return array('id'); }

	static $id_fields_cache = array();
	function id_field()
	{
		$class_name = $this->class_name();
		if(!empty(self::$id_fields_cache[$class_name]))
			return self::$id_fields_cache[$class_name];

		if(method_exists($this, 'table_fields') && $this->storage_engine() != 'storage_db_mysql_smart')
		{
			// Новый формат
			foreach(bors_lib_orm::main_fields($this) as $f)
			{
				if($f['property'] == 'id')
				{
					self::$id_fields_cache[$class_name] = $f['name'];
					return $f['name'];
				}
			}
		}

		$ff = $this->fields_map();

		if(count($ff) == 1) //FIXME: костыль для поддержки древних field() методов.
							//В списке полей одна запись, если по дефолту прочиталось function table_fields() { return array('id'); }
			$ff = array_shift(array_shift($this->fields()));

		if($id_field = @$ff['id'])
		{
			if(is_array($id_field))
			{
				bors_lib_orm::field('id', $id_field);
				self::$id_fields_cache[$class_name] = $id_field['name'];
				return $id_field['name'];
			}
			else
			{
				self::$id_fields_cache[$class_name] = $id_field;
				return $id_field;
			}
		}

		//FIXME: исправить на возможность id в ненулевой позиции
		$id_field = @$ff[0] == 'id' ? 'id' : NULL;
		self::$id_fields_cache[$class_name] = $id_field;
		return $id_field;
	}

	function title_field()
	{
		if(method_exists($this, 'table_fields') && $this->storage_engine() != 'storage_db_mysql_smart')
		{
			// Новый формат
			foreach(bors_lib_orm::main_fields($this) as $f)
			{
				if($f['property'] == 'title')
					return $f['name'];
			}
		}

		return defval($this->fields_map(), 'title', 'title');
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

	var $___args = array();
	function set_args($args) { return $this->___args = $args; }
	function _set_arg($name, $value) { return $this->___args[$name] = $value; }
	function args($name=false, $def = NULL) { return $name ? $this->arg($name, $def) : $this->___args; }
	function arg($name, $def = NULL) { return array_key_exists($name, $this->___args) ? $this->___args[$name] : $def; }

	function was_cleaned() { return !empty($GLOBALS['bors_obect_self_cleaned'][$this->internal_uri()]); }
	function set_was_cleaned($value) { return $GLOBALS['bors_obect_self_cleaned'][$this->internal_uri()] = $value; }

	function touch($user_id, $timestamp = NULL) { }

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
//		echo "set visit";
//		echo debug_trace();
		$this->set_last_visit_time($time, true);
	}

	function pre_action(&$data) { return false; }
	function need_access_level() { return 0; }
	function cache_life_time() { return 0; }

	private function ___path_data()
	{
		if($this->__havefc())
			return $this->__lastc();
		$path = $this->called_url();
		if(!$path)
			$path = $this->id();

		return $this->__setc(url_parse($path));
	}

	function dir()
	{
		$data = $this->___path_data();

		return preg_match('!^(.+)/$!', $data['local_path'], $m) ? $m[1] : dirname($data['local_path']);
	}

	function _basename()
	{
		$data = $this->___path_data();
		return preg_match('!^(.+)/$!', $data['local_path'], $m) ? '' : basename($data['local_path']);
	}

	function document_root()
	{
		$data = url_parse($this->called_url());
		return @$data['root'];
	}

	function host()
	{
		$data = url_parse($this->called_url());
		return $data['host'];
	}

	function set_class_file($file_name)
	{
		bors_class_loader::$class_files[$this->class_name()] = $file_name;
		bors_class_loader::$class_file_mtimes[$this->class_name()] = filemtime($file_name);
		return $file_name;
	}

	function class_file() { return bors_class_loader::file($this->class_name()); }
	function class_filemtime() { return @bors_class_loader::$class_file_mtimes[$this->class_name()]; }

	function real_class_file() { return @bors_class_loader::$class_files[$this->class_name()]; }
	function class_dir() { return dirname($this->class_file()); }

	function pre_set(&$data)
	{
		if($tgs = popval($data, 'linked_targets'))
		{
			foreach(explode(',', $tgs) as $t)
			{
				if($target_link = popval($data, $t))
					$data[$t] = object_load($target_link);
				else
					$data[$t] = NULL;
			}
		}

		// «Перезаписывающие» поля
		if($overrides = popval($data, 'override_fields'))
		{
			foreach(explode(',', $overrides) as $name)
			{
				// формат 'func(name1+name2)' — обработка функцией для _name и добавление к результату name
				if(preg_match('!^(\w+)\((\w+)\+(\w+)\)$!', $name, $m))
				{
					$func = $m[1];
					if(!$func == 'bors_comma_join')
						bors_throw(ec('Ещё не реализовано')); // Добавить проверку безопасности
					$data[$m[2]] = $func(@$data[$m[2]], @$data[$m[3]]);
					unset($data[$m[3]]);
				}
				// формат 'func(name)' — прямая обработка функцией
				elseif(preg_match('!^(\w+)\((\w+)\)$!', $name, $m))
				{
					$func = $m[1];
					bors_throw(ec('Ещё не реализовано')); // Добавить проверку безопасности
					$data[$m[2]] = $func($data['_'.$m[2]]);
				}
				// !_origin_id
				// Прямое присваивание при ненулевом значении
				elseif(preg_match('/^!_(\w+)$/', $name, $m))
				{
					if($val = popval($data, '_'.$m[1]))
						$data[$m[1]] = $val;
				}
				// Прямое присваивание.
				else
				{
					if(array_key_exists($key = '_'.$name, $data))
						$data[$name] = $data[$key];
				}
			}
		}
	}

	// Именно по ссылке, т.к. можно менять 'go' и т.п.
	function post_set(&$data) { }
	function post_save(&$data) { }

	function on_new_instance(&$data)
	{
		$this->__update_relations();
	}

	function static_get_cache() { return false; }

	function change_time($exactly = false)
	{
		$changed = max($this->create_time(true), $this->modify_time(true));
		return $changed || $exactly ? $changed : time();
	}

	// Класс, который будет записываться в системах учёта ссылок.
	// Если где-то в статистике, например, показывается ссылка на
	// view-класс, а учитывать нужно базовый объект, для которого
	// вызывается view, то $base_class = $view->referent();
	function referent_class() { return $this->class_name(); }

	function extends_class_name() { return $this->class_name(); }
	function new_class_name() { return $this->class_name(); }

	function extends_class_id()
	{
		if($this->__havefc())
			return $this->__lastc();

		return $this->__setc(class_name_to_id($this->extends_class_name()));
	}

	function direct_content()
	{
		if(!($render_engine = $this->render_engine()))
			return NULL;

		if(config('debug.execute_trace'))
			debug_execute_trace("{$this->debug_title_short()} render engine = '$render_engine' (old direct_content)");

		if($render_engine == 'self')
			$re = $this;
		elseif(!($re = object_load($render_engine)))
			debug_exit("Can't load global render engine {$render_engine} for object '{$this}'");

		return $re->render($this);
	}

	function index_file() { return 'index.html'; }

	function static_file()
	{
		$path = $this->url_ex($this->args('page'));
		$data = url_parse($path);

		$file = @$data['local_path'];
		if(preg_match('!/$!', $file))
			$file .= $this->index_file();

		$rel_file = @$data['path'];
		if(preg_match('!/$!', $rel_file))
			$rel_file .= $this->index_file();

		if($r = $this->get('cache_static_root'))
			$file = $r.$rel_file;
		elseif($r = config('cache_static.root'))
			$file = str_replace($_SERVER['DOCUMENT_ROOT'], $r, $file);

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

		return  ec($str, $this->files_charset());
	}

	function cs_d2i($str) { return @iconv($this->db_charset(), $this->internal_charset().'//IGNORE', $str); }

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

		return @iconv($this->internal_charset(), $out_cs.'//IGNORE', $str);
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

	function hcom($msg)
	{
//		if(preg_match('/\.html$/', $this->url()))
//			echo "<!-- $msg -->\n";
	}

	function content()
	{
		$recreate = $this->get('recreate_on_content') || $this->get('cache_static_recreate');

		$use_static = config('cache_static')
			&& ($recreate || $this->cache_static() > 0);

		$this->hcom("rcr=$recreate; static=$use_static");

		$file = $this->static_file();
		$fe = file_exists($file);
		$fs = $fe && filesize($file) > 2000;
		$file_fresh = $fe && $this->modify_time()
			&& filemtime($file) >= $this->modify_time()
			&& filemtime($file) >= $this->class_filemtime();

		if(!empty($_GET) && array_key_exists('nc', $_GET))
			$file_fresh = false;

		$this->hcom("f=$file, fe=$fe, fresh=$file_fresh (mt={$this->modify_time()}, fm=".@filemtime($file).")");
		if($use_static && $file && $fe && !$recreate && $file_fresh)
			return file_get_contents($file);

//		$mem = new \Jamm\Memory\RedisObject('dog-pile-cacher', '192.168.1.3');

		$stash_item = NULL;
		if($this->id() && !is_object($this->id()) && $this->modify_time() && ($pool = config('cache.stash.pool')))
		{
			$stash_item = $pool->getItem('dog-pill-protect/'.$this->internal_uri_ascii().'/'.$this->page().'/'.$this->modify_time());

			$content = $stash_item->get(Stash\Invalidation::SLEEP, 300, 100);

			if(!$stash_item->isMiss())
				return $content;

			$stash_item->lock();
			$this->hcom("stash locked");
		}


		if(0 && $use_static
			&& !$fs
			&& $this->use_temporary_static_file()
			&& config('temporary_file_contents')
			&& !file_exists($this->static_file())
		)
		{
			$this->hcom("tmp: {$this->static_file()}");

			cache_static::save_object($this, /*$this->cs_i2o*/(str_replace(array(
				'$url',
				'$title',
				'$charset',
			), array(
				$this->url_ex($this->page()),
				$this->title(),
				$this->output_charset(),
			), $this->cs_u2i(config('temporary_file_contents')))), 120);

			$this->hcom("tmp fs= ".filesize($this->static_file()));
		}

		if(config('debug.execute_trace'))
			debug_execute_trace("{$this->debug_title_short()}->direct_content()");

		$this->hcom("get direct content");
		$content = $this->direct_content();

		if($this->internal_charset() != $this->output_charset())
			$output_content = $this->cs_i2o($content);
		else
			$output_content = $content;

		if($stash_item)
			$stash_item->set($content, 10);

		if(empty($content) && $use_static)
		{
			cache_static::drop($this);
			return '';
		}

//		echo "cs=$use_static, recreate=$recreate";
//		if($use_static || $recreate)
		if($use_static)
			cache_static::save_object($this, $content);

		if(config('use_memcached_objects') || $recreate)
			bors_objects_helper::cache_registers($this);

		return $output_content;
	}

	function object_title() { return strip_tags(bors_lower($this->class_title()).ec(' «').$this->title().ec('»')); }
	function object_titled_url() { return $this->class_title().ec(' «').$this->titled_url().ec('»'); }
	function object_titled_rp_link() { return $this->class_title_rp().ec(' «').$this->titled_link().ec('»'); }
	function object_titled_vp_link() { return $this->class_title_vp().ec(' «').$this->titled_link().ec('»'); }

	function object_title_dp() { return strip_tags(bors_lower($this->class_title_dp()).ec(' «').$this->title().ec('»')); }
	function object_titled_dp_link() { return $this->class_title_dp().ec(' «').$this->titled_link().ec('»'); }

	function cross_ids($to_class) { return bors_link::object_ids($this, $to_class); }
	function cross_objs($to_class = NULL) { return bors_link::objects($this, $to_class); }
	function cross_links($params = array()) { return bors_link::links($this, $params); }
	function cross_objects($to_class = NULL) { return bors_link::objects($this, $to_class); }
	function add_cross($class, $id, $order = 0) { return bors_link::link($this->extends_class_id(), $this->id(), $class, $id, array('sort_order' => $order)); }
	function add_cross_object($object, $order = 0) { return bors_link::link_objects($this, $object, array('sort_order' => $order)); }

	function cross_remove_object($obj) { bors_link::drop_target($this, $obj); }

	function add_link_to($target, $params = array()) { bors_link::link_object_to($this, $target, $params); }

	function on_action_link(&$data)
	{
		if(empty($data['link_object_from']))
			$obj = $this;
		else
			$obj = object_load($data['link_object_from']);

		if(!$obj)
			return bors_exit(ec('Не существующий привязывающий объект ').$data['link_object_from']);

		if(!empty($data['link_class_name']) && !empty($data['link_object_id']))
		{
			$target = object_load($data['link_class_name'], $data['link_object_id']);

			if(!$target)
				return bors_exit(ec('Не существующий привязываемый объект ').$data['link_class_name'].'://'. $data['link_object_id']);

			$obj->add_cross_object($target);
		}

		if(!empty($data['link_urls']))
		{
			foreach(explode("\n", $data['link_urls']) as $url)
			{
				$target = bors_load_uri(trim($url));

				if(!$target)
					bors_throw(ec('Не существующий привязываемый объект ').$data['link_urls']);

				if($t2 = $target->get('target'))
					$target = $t2;

				$obj->add_cross_object($target);
			}
		}
	}

	function default_page() { return 1; }

	function page() { return $this->attr('page'); }
	function set_page($page) { return $this->set_attr('page', $page ? $page : $this->default_page()); }

	function empty_id_handler() { return NULL; }
	function auto_assign_all_fields() { return false; }

	function link_time1() { return NULL; }
	function link_time2() { return NULL; }

//	function __toString() { return $this->class_name().'://'.$this->id().($this->page() > 1 ? ','.$this->page() : ''); }
	function __toString()
	{
		if($tt = $this->title_true())
			return $this->class_title().ec(' «').$tt.ec('»').(is_numeric($this->id()) ? "({$this->id()})" : '');

		return $this->class_name().'://'.$this->id().($this->page() > 1 ? ','.$this->page() : '');
	}

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

		if(preg_match('/text/', $field_name))
			return 'text';

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

	function delete()
	{
		delete_cached_object($this);
		return false;
	}

	function imaged_titled_desc_admin_link()
	{
		if($img = $this->get('image'))
			$img = $img->thumbnail('72x48(up,crop)')->html_code('class="float-left pull-left" style="margin-right: 4px"');
		else
			$img = "";

		return "{$img}<b>{$this->admin()->imaged_titled_link()}</b><br/>"
			."<small class=\"muted\">".bors_truncate($this->description(), 200)."</small>";
	}
}
