<?php

require_once('engines/lcml/main.php');

class base_page extends bors_object
{
	function render_engine() { return config('render_engine', 'render_page'); }
	function storage_engine() { return NULL; }
	function can_be_empty() { return true; }

	function class_title()		{ return ec('Страница'); }
	function class_title_rp()	{ return ec('страницы'); }
	function class_title_dp()	{ return ec('странице'); }
	function class_title_vp()	{ return ec('страницу'); }

	function page_title()		{ return $this->get('page_title', $this->title(), true); }

	function browser_title()
	{
		if($t = $this->get('browser_title', NULL, true))
			return $t;

		return $this->title();
	}

	function source() { return @$this->data['source']; }
	function set_source($source, $db_update) { return $this->set('source', $source, $db_update); }

	function me() { return bors()->user(); }
	function me_id() { return bors()->user_id(); }

	function items_around_page() { return 10; }

	function attr_preset()
	{
		return array_merge(parent::attr_preset(), array(
			'cr_type'	=> '',
			'body_engine' => 'bors_bodies_page',
			'body_template_class' => 'bors_templates_smarty',
			'visits' => 0,
			'num_replies' => 0,
		));
	}

	function is_reversed() { return false; }

	function pages_links_list($css='pages_select', $before='', $after='')
	{
		if($this->total_pages() < 2)
			return '';

        include_once("inc/design/page_split.php");
		$pages = '<li>'.join('</li><li>', pages_show($this, $this->total_pages(), $this->items_around_page())).'</li>';
		return '<div class="'.$css.'">'.$before.ec('<ul><li>Страницы:</li>').$pages.'</ul>'.$after.'</div>';
	}

	function pages_links($css='pages_select', $text = NULL, $delim = '', $show_current = true, $use_items_numeration = false, $around_page = NULL)
	{
		return $this->pages_links_nul($css, $text, $delim, $show_current, $use_items_numeration, $around_page);
	}

	function pages_links_nul($css='pages_select', $text = NULL, $delim = '', $show_current = true, $use_items_numeration = false, $around_page = NULL)
	{
		if($this->total_pages() < 2)
			return '';

		if($text === NULL)
			$text = ec('Страницы:');

		include_once('inc/design/page_split.php');

		if(!$around_page)
			$around_page = $this->items_around_page();

		$pages = pages_show($this, $this->total_pages(), $around_page,
			$show_current, 'current_page', 'select_page',
			$use_items_numeration, $this->items_per_page(), $this->total_items()
		);

		if($this->is_reversed())
			$pages = array_reverse($pages);

		return '<div class="'.$css.'">'.$text.join($delim, $pages).'</div>';
	}

	function getsort($t, $def = false)
	{
		$sort = @$_GET['s'];

		$r = intval(@$_GET['r']);
		if($t == $sort)
			$r = ($r ? 0 : 1);
		else
			$r = 0;

		return "s={$t}" . ($r ? '&r=1' : '');
	}

	function total_pages()
	{
		if($this->__havefc())
			return $this->__lastc();

		$total = $this->total_items();
		return $this->__setc($total >= 0 ? intval(($total - 1)/$this->items_per_page()) + 1 : 1);
	}

	function items_per_page() { return 25; }
	private $__total_items = -1;
	function total_items() { return $this->__total_items; }
	function set_total_items($count) { return $this->__total_items = $count; }
	function items_offset() { return ($this->page()-1)*$this->items_per_page(); }

	function body()
	{
		if($this->__havefc())
			return $this->__lastc();

		if($body_class_name = $this->body_engine())
		{
			$body_engine = bors_load($body_class_name, NULL);
			if(!$body_engine)
				bors_throw("Can't load body engine '{$body_class_name}' for class {$this}");

			return $this->__setc($body_engine->body($this));
		}

		bors_throw("Not defined body engine for class '{$this}'");

		// Дальше — obsolete, пока не сносим, вдруг понадобится что-то

		global $me;

		if($this->need_access_level() > 1 && $this->need_access_level() > $me->get("level"))
		{
			require_once("funcs/modules/messages.php");
			return error_message(ec("У Вас недостаточный уровень доступа для этой страницы. Ваш уровень ").$me->get("level").ec(", требуется ").$this->need_access_level());
		}

		if(!$this->cache_life_time())
			return $this->cacheable_body();

		$ch = new Cache();

		$drop_cache = $this->cache_life_time() || !empty($_GET['drop_cache']);

		if($ch->get('bors-cached-body-v18', $this->internal_uri()) && !$drop_cache)
		{
			$add = "\n<!-- cached; create=".strftime("%d.%m.%Y %H:%M", $ch->create_time)."; expire=".strftime("%d.%m.%Y %H:%M", $ch->expire_time)." -->";
			return $ch->last().$add;
		}

		$content = $ch->set($this->cacheable_body(), $this->cache_life_time());

		// Зарегистрируем сохранённый кеш в группах кеша, чтобы можно было чистить
		// при обновлении данных, от которых зависит наш контент

		foreach(explode(' ', $this->cache_depends_on()) as $group)
			if($group)
				cache_group::register($group, $this);

		return $this->attr['body'] = $content;
	}

	function dis_cacheable_body()
	{
		$data = array();

		//TODO: Вычистить все _queries.
		if($qlist = $this->_queries())
		{
			// Привязка к БД АвиаПорта, так как нигде в других проектах не используется и не должно использоваться.
			$db = new driver_mysql(config('aviaport_db'));

			foreach($qlist as $qname => $q)
			{
				$cache = false;
				if(preg_match("!^(.+)\|(\d+)$!s", $q, $m))
				{
					$q		= $m[1];
					$cache	= $m[2];
				}

				if(preg_match("/!(.+)$/s", $q, $m))
					$data[$qname] = $db->get($m[1], false, $cache);
				else
					$data[$qname] = $db->get_array($q, false, $cache);
			}
		}
		$data['template_dir'] = $this->class_dir();
		$data['this'] = $this;

		$this->template_data_fill();
//		require_once('engines/smarty/assign.php');
		$data['compile_id'] = $this->class_name();
		$result = bors_templates_smarty::fetch_ex($this->body_template(), $data);// template_assign_data($this->body_template(), $data);
		return $result;
	}

	function compiled_source() { return lcml($this->source()); }

	function _queries() { return array(); }

	function body_template_ext() { return 'html'; }

	function body_template()
	{
		$current_class = get_class($this);
		$class_files = $GLOBALS['bors_data']['classes_included'];
		$ext = $this->body_template_ext();

		while($current_class)
		{
			$template_file = preg_replace("!(.+/\w+)\..+?$!", "$1.$ext", $class_files[$current_class]);
			if(file_exists($template_file))
				break;
			$current_class = get_parent_class($current_class);
		}

		return "xfile:{$template_file}";
	}

	function nav_name()
	{
		if(($nav = parent::nav_name()))
			return $nav;

		return $this->id() ? $this->class_title() : '';
	}

	function pre_show()
	{
		@header('Content-Type: text/html; charset='.config('output_charset', config('internal_charset', 'utf-8')));
		@header('Content-Language: '.config('page_lang', 'ru'));

		return parent::pre_show();
	}

	// TODO: найти использование и снести под children_string
	function children_list() { return join("\n", $this->children())."\n"; }
	function set_children_list($value, $dbup) { return $this->set_children($value ? explode("\n", trim($value)) : array(), $dbup); }

	function lcml($text)
	{
		if(!$text)
			return;

		$ch = (class_exists('Cache') && !config('lcml_cache_disable')) ? new Cache() : NULL;
		if($ch && $ch->get('base_object-lcml', $text) && 0)
			return $ch->last();

		$save_lcml_tags_enabled = config('lcml_tags_enabled');
		config_set('lcml_tags_enabled', $this->lcml_tags_enabled());
		$text = lcml($text,
			array(
				'cr_type' => $this->cr_type(),
				'sharp_not_comment' => $this->sharp_not_comment(),
				'html_disable' => $this->html_disable(),
		));
		config_set('lcml_tags_enabled', $save_lcml_tags_enabled);

		if($ch)
			$ch->set($text, 7*86400);

		return $text;
	}

	function sharp_not_comment() { return true; }

	private $_html_disable = NULL;
	function set_html_disable($value) { return $this->_html_disable = $value; }
	function html_disable()
	{
		if($this->_html_disable === NULL)
			$this->_html_disable = !config('lcml_source_html_enabled');

		return $this->_html_disable;
	}

	private $_lcml_tags_enabled = -1;
	function set_lcml_tags_enabled($value) { return $this->_lcml_tags_enabled = $value; }
	function lcml_tags_enabled()
	{
		if($this->_lcml_tags_enabled === -1)
			$this->_lcml_tags_enabled = config('lcml_tags_enabled');

		return $this->_lcml_tags_enabled;
	}

	function template_vars() { return parent::template_vars().' browser_title'; }

	function check_value_conditions()
	{
		return array_merge(parent::check_value_conditions(), array(
//			'title'	=> ec("!=''|Заголовок ".$this->class_title_rp()." должен быть указан"),
//			'source'=> ec("!=''|Текст ".$this->class_title_rp()." должен быть задан"),
		));
	}

	function editor_fields_list()
	{
		return array(
			ec('Полный заголовок '.$this->class_title_rp().':') => 'title',
			ec('Краткий заголовок '.$this->class_title_rp().':') => 'nav_name',
			ec('Краткое описание:') => 'description|textarea=2',
			ec('Текст:') => 'source|textarea=20',
			ec('Тип перевода строк:') => 'cr_type|dropdown=common_list_crTypes',
		);
	}

	function merge_template_data_array($key, $merge_values)
	{
		$prev = self::template_data($key);
		if(!$prev)
			$prev = array();
		self::add_template_data($key, array_merge($prev, $merge_values));
	}

	function prepend_template_data_array($key, $prepend_values)
	{
		$prev = self::template_data($key);
		if(!$prev)
			$prev = array();
		self::add_template_data($key, array_merge($prepend_values, $prev));
	}

	function keywords_linked() { return ''; }

	function search_source($include_headers = true)
	{
		$result = array();
		if($include_headers)
		{
			$result[] = $this->title();
			$result[] = $this->description();
		}

		return join("\n\n", array_merge($result, array(
			$this->source(),
		)));
	}

	function page_data() { return array(); }
	function body_data() { return array(); }

	function global_data() { return array_merge(parent::global_data(), $this->page_data()); }
	function local_data()  { return array_merge(parent::local_data(), $this->body_data()); }
}
