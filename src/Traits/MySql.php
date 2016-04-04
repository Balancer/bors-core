<?php

namespace B2\Traits;

trait MySql
{
	function db($database_name = NULL)
	{
		if(empty($this->_dbh))
		{
			if(!$database_name)
				 $database_name = $this->get('db_name');
			if(!$database_name)
				 $database_name = config('main_bors_db');

			$this->_dbh = new \driver_mysql($database_name);
		}

		return $this->_dbh;
	}

	//TODO: разобраться с сериализацией приватных данных
	public function __sleep()
	{
		if(!empty($this->_dbh))
		{
			$this->_dbh->close();
			$this->_dbh = NULL;
		}

		return parent::__sleep();
	}

	/** Вернуть карту полей объекта для главной таблицы (если их несколько) */
	function fields_map() { return $this->table_fields(); }

	function table_fields() { return array('id'); }

//	static $id_fields_cache = array();
	function id_field()
	{
		$class_name = $this->class_name();
		if(!empty(self::$id_fields_cache[$class_name]))
			return self::$id_fields_cache[$class_name];

		if(method_exists($this, 'table_fields') && $this->storage_engine() != 'storage_db_mysql_smart')
		{
			// Новый формат
			foreach(\bors_lib_orm::main_fields($this) as $f)
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

//	var $___args = array();
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
//		echo bors_debug::trace();
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

	// Всегда возвращает путь без последнего слова, независимо от того, оканчивается ли ссылка на '/'
	// Для корневого элемента возвращает doc_root;
	function dir()
	{
		$data = $this->___path_data();

		if($data['local_path'] == @$data['root'].'/')
			return rtrim($data['local_path'], '/');

		return dirname(rtrim($data['local_path'], '/'));
	}

	// Всегда возвращает последнее слово из пути, независимо от того, оканчивается ли ссылка на '/'.
	// Для корневого пути возвращает пустую строку.
	function _basename()
	{
		$data = $this->___path_data();
		if($data['local_path'] == @$data['root'].'/')
			return '';

		return basename(rtrim($data['local_path'], '/'));
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
		\bors_class_loader::$class_files[$this->class_name()] = $file_name;
		\bors_class_loader::$class_file_mtimes[$this->class_name()] = filemtime($file_name);
		return $file_name;
	}

	function class_file() { return \bors_class_loader::file($this->class_name()); }
	function class_filemtime() { return @\bors_class_loader::$class_file_mtimes[$this->class_name()]; }

	function real_class_file() { return @\bors_class_loader::$class_files[$this->class_name()]; }
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
		if(method_exists($this, '__update_relations'))
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
        $renderer = $this->renderer();
        if(config('debug.execute_trace'))
            debug_execute_trace("{$this->debug_title_short()} renderer = {$renderer}");

        if($renderer)
            return $renderer->render($this);

        $view_class = $this->get('view_class');
        if($view_class && ($view = bors_load($view_class, $this)))
        {
            $view->set_model($this);
            return $view->content();
        }

		if(!($render_engine = $this->render_engine()))
			return NULL;

		if(config('debug.execute_trace'))
			debug_execute_trace("{$this->debug_title_short()} render engine = '$render_engine' (old direct_content)");

		if($render_engine == 'self')
			$re = $this;
		elseif(!($re = bors_load($render_engine)))
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
			&& !config('skip_cache_static')
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
			$stash_item = $pool->getItem('dog-pill-protect:'.$this->internal_uri_ascii().':'.$this->page().':'.$this->modify_time());

			$content = $stash_item->get(\Stash\Invalidation::SLEEP, 300, 100);

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
			\cache_static::drop($this);
			return '';
		}

//		echo "cs=$use_static, recreate=$recreate";
//		if($use_static || $recreate)
		if($use_static)
			\cache_static::save_object($this, $content);

		if(config('use_memcached_objects') || $recreate)
			\bors_objects_helper::cache_registers($this);

		return $output_content;
	}

	function object_title() { return strip_tags(bors_lower($this->class_title()).ec(' «').$this->title().ec('»')); }
	function object_titled_url() { return $this->class_title().ec(' «').$this->titled_url().ec('»'); }
	function object_titled_rp_link() { return $this->class_title_rp().ec(' «').$this->titled_link().ec('»'); }
	function object_titled_vp_link() { return $this->class_title_vp().ec(' «').$this->titled_link().ec('»'); }

	function object_title_dp() { return strip_tags(bors_lower($this->class_title_dp()).ec(' «').$this->title().ec('»')); }
	function object_titled_dp_link() { return $this->class_title_dp().ec(' «').$this->titled_link().ec('»'); }

	function cross_ids($to_class) { return \bors_link::object_ids($this, $to_class); }
	function cross_objs($to_class = NULL) { return \bors_link::objects($this, $to_class); }
	function cross_links($params = array()) { return \bors_link::links($this, $params); }
	function cross_objects($to_class = NULL) { return \bors_link::objects($this, $to_class); }
	function add_cross($class, $id, $order = 0) { return \bors_link::link($this->extends_class_id(), $this->id(), $class, $id, array('sort_order' => $order)); }
	function add_cross_object($object, $order = 0) { return \bors_link::link_objects($this, $object, array('sort_order' => $order)); }

	function cross_remove_object($obj) { \bors_link::drop_target($this, $obj); }

	function add_link_to($target, $params = array()) { \bors_link::link_object_to($this, $target, $params); }

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

	function __toString()
	{
		try
		{
			if($tt = $this->title_true())
				return $this->class_title().ec(' «').$tt.ec('»').(is_numeric($this->id()) ? "({$this->id()})" : '');
		}
		catch(Exception $e)
		{
		}

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

	function add_keyword($keyword, $up = true)
	{
		$keyword = trim($keyword);
		$keywords = $this->keywords();
		foreach($keywords as $kw)
		{
			$kw = trim($kw);
			if(\common_keyword::compare_eq($kw, $keyword))
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
			if(!\common_keyword::compare_eq($kw, $keyword))
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

	function this() { return $this; }
	function is_value() { return true; }
	function can_have_cross() { return NULL; }

	function bors_di_classes() { return array(); }

/*
	function show()
	{
//		if($go = $obj->attr('redirect_to'))
//			return go($go);

		return false; // Пока ничего автоматом не выводим
		//TODO: добавить debug-info в конец и т.п. вещи из main.php
//		echo $this->content();
//		return true;
	}
*/

	// возвращает полное содержимое объекта для вывода в браузер. Некешированное.
	function __content() // пока не используется, т.к. более древнее в base_object
	{
		if(($render_class = $this->render_class()))
		{
			if($render_class == 'self')
				$render = $this;
			elseif(!($render = bors_load($render_class)))
				debug_exit("Can't load global render engine {$render_class} for object '{$this}'");

			return $render->rendering($this);
		}
	}

    /**
     * @param int|string $id
     * @return bors_object|b2_null
     */
    static function load($id)
	{
		$object = bors_load(get_called_class(), $id);

		if(!$object)
			$object = new b2_null(NULL);

		return $object;
	}

    static function create($data)
	{
		return bors_new(get_called_class(), $data);
	}

	function renderer()
	{
		$renderer_class = $this->get('theme_class');

		if(!$renderer_class)
			$renderer_class = $this->get('renderer_class');

		if(!$renderer_class)
			$renderer_class = $this->get('render_engine'); // Старый API, для совместимости.

		if($renderer_class == 'self')
			return $this;

		if(!$renderer_class)
			return NULL;

		$renderer = bors_load($renderer_class, $this);
		if(!$renderer)
			bors_throw("Can't load theme renderer $renderer_class");

		if($layout_class = $renderer->get('layout_class'))
			$this->set_attr('layout_class', $layout_class);

		return $renderer;
	}

	function description_or_title()
	{
		if($desc = $this->description())
			return $desc;

		if($title = $this->title())
			return $title;

		return ec('[без имени]');
	}

	function admin_additional_info() { return array(); }

	function object_type() { return 'unknown'; }

	function ctime()
	{
		if($this->__havefc())
			return $this->__lastc();

		return bors_load('bors_time', $this->create_time());
	}

	function _admin_searchable_title_properties_def()
	{
		return $this->___admin_searchable_properties_def(false);
	}

	function _admin_searchable_properties_def()
	{
		return $this->___admin_searchable_properties_def(true);
	}

	function ___admin_searchable_properties_def($any)
	{
		$properties = array();
		$title_properties = array();
		foreach(\bors_lib_orm::fields($this) as $x)
			if(!empty($x['is_admin_searchable']))
				if(empty($x['is_title']))
					$properties[] = $x['property'];
				else
					$title_properties[] = $x['property'];

		if($title_properties && !$any)
			$properties = $title_properties;

		if(!$properties)
		{
			$properties[] = 'id';
			$properties[] = 'title';
		}

		if($x = $this->get('searchable_title_properties'))
			$properties += $x;

		if($any && ($x = $this->get('searchable_more_properties')))
			$properties += $x;

		return join(' ', array_unique($properties));
	}

	function _section_name_def() { return \bors_core_object_defaults::section_name($this); }
	function _is_changes_logging_def() { return false; }

	function call($method_name)
	{
		$args = func_get_args();
		array_shift($args);
//		var_dump($method_name, $args);
		if(method_exists($this, $method_name))
			return call_user_func_array(array($this, $method_name), $args);

		return NULL;
	}

	function _item_list_admin_fields_def() { return $this->get('item_list_fields'); }

	function module($module_name) { return $this->module_ex($module_name, array()); }

	// http://aviaport.wrk.ru/directory/aviafirms/groups/
	function module_ex($module_name, $attrs)
	{
		$class_name = $this->class_name();
		$class_name = preg_replace('/_(main|edit|view)$/', '', $class_name);
		$module_class = \blib_grammar::plural($class_name).'_modules_'.$module_name;
		set_def($attrs, 'model', $this);
		$mod = bors_load_ex($module_class, $this, $attrs);
		if(!$mod)
			bors_throw(ec("Не могу загрузить модуль '$module_class'"));

		return $mod;
	}

	function uses($asset, $args = NULL)
	{
//		if($asset == 'composer')
//			return require_once(__DIR__.'/../../../../autoload.php');

		bors_throw("Unknown uses $asset");
//		return parent::uses($asset, $args);
	}

	// Добавить свойства другого объекта к свойствам нашего
	function _set_prop_join($join_object)
	{
		$this->_prop_joins[] = $join_object;
	}

	function __class_cache_base()
	{
		return config('cache_dir').'/classes/'.str_replace('_', '/', get_class($this));
	}

//	static $__cache_data = array();
	function class_cache_data($name = NULL, $setter = NULL)
	{
		if(empty(self::$__cache_data[$this->class_name()]))
		{
			if(file_exists($f = $this->__class_cache_base().'.data.json'))
				$data = json_decode(file_get_contents($f), true);

			if(empty($data['class_mtime']) || $data['class_mtime'] != filemtime($this->class_file()))
				self::$__cache_data[$this->class_name()] = $data = array();
			else
				self::$__cache_data[$this->class_name()] = $data;
		}

		if(!$name)
			return empty(self::$__cache_data[$this->class_name()]) ? array() : self::$__cache_data[$this->class_name()];

		if(!empty(self::$__cache_data[$this->class_name()]) && array_key_exists($name, self::$__cache_data[$this->class_name()]))
			return self::$__cache_data[$this->class_name()][$name];

		if($setter)
			return $this->set_class_cache_data($name, call_user_func($setter));

		return NULL;
	}

	function set_class_cache_data($name, $value)
	{
		return \bors_class_loader::set_class_cache_data($this->class_name(), $this->class_file(), $name, $value);
	}

	function property_info($property_name)
	{
		foreach(bors_lib_orm::all_fields($this) as $f)
			if($f['property'] == $property_name)
				return $f;

		return bors_throw("Can't find property $property_name in ".$this->class_name());
	}

	static function find($where = array())
	{
		$class_name = get_called_class();
		$finder = new \b2_core_find($class_name);

		if($where)
			$finder->where($where);

		return $finder;
	}

	function uuid_hash() { return md5($this->class_name().':'.$this->id()); }

	function titled_link_for_igo()
	{
		$title = $this->title_in_container();

		return "<a href=\"{$this->url_for_igo()}\">{$title}</a>";
	}
}
