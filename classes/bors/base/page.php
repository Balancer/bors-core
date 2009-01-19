<?php

require_once('engines/lcml.php');

class base_page extends base_object
{
	function render_engine() { return 'render_page'; }
	function storage_engine() { return NULL; }
	function can_be_empty() { return true; }
	
	var $stb_source = NULL;
	function set_source($source, $db_update) { $this->set("source", $source, $db_update); }
	function source() { return $this->stb_source; }

	function items_around_page() { return 10; }

	function pages_links($css='pages_select', $before='', $after='')
	{

		if($this->total_pages() < 2)
			return '';
		
		include_once("inc/design/page_split.php");
		$pages = '<li>'.join('</li><li>', pages_show($this, $this->total_pages(), $this->items_around_page())).'</li>';
		return '<div class="'.$css.'">'.$before.ec('<ul><li>Страницы:</li>').$pages.'</ul>'.$after.'</div>';
	}

	function pages_links_nul($css='pages_select', $text = NULL, $delim = '', $show_current = true)
	{
		if($this->total_pages() < 2)
			return '';

		if($text === NULL)
			$text = ec('Страницы:');

		include_once('inc/design/page_split.php');
		return '<div class="'.$css.'">'.$text.join($delim, pages_show($this, $this->total_pages(), $this->items_around_page(), $show_current)).'</div>';
	}

	function getsort($t, $def = false)
	{
		$sort = @$_GET['s'];
		if(!$sort)
			$sort = $t;
		
		$r = intval(@$_GET['r']);
		if($t == $sort)
			$r = ($r ? 0 : 1);
		else
			$r = 0;
			
		return "s={$t}" . ($r ? '&r=1' : '');
	}

	private $_total_pages = false;
	function total_pages()
	{
		if($this->_total_pages === false)
			$this->_total_pages = ($total = $this->total_items()) >= 0 ? intval(($total - 1)/$this->items_per_page()) + 1 : 1;
		
		return $this->_total_pages;
	}

	function items_per_page() { return 25; }
	function total_items() { return -1; }
	function items_offset() { return ($this->page()-1)*$this->items_per_page(); }

	var $stb_body = false;
	function body()
	{
		if($this->stb_body !== false)
			return $this->stb_body;
	
		if($body_engine = $this->body_engine())
		{
			$be = class_load($body_engine);
			if(!$be)
				debug_exit("Can't load body engine {$body_engine} for class {$this}");

			return $this->stb_body = $be->body($this);
		}
			
		global $me;
		
		if($this->need_access_level() > 1 && $this->need_access_level() > $me->get("level"))
		{
			require_once("funcs/modules/messages.php");
			return error_message(ec("У Вас недостаточный уровень доступа для этой страницы. Ваш уровень ").$me->get("level").ec(", требуется ").$this->need_access_level());
		}
			
		if(!$this->cache_life_time())
			return $this->cacheable_body();
			
		$ch = &new Cache();
			
		$drop_cache = $this->cache_life_time() || !empty($_GET['drop_cache']);
			
		if($ch->get('bors-cached-body-v18', $this->internal_uri()) && !$drop_cache)
		{
			$add = "\n<!-- cached; create=".strftime("%d.%m.%Y %H:%M", $ch->create_time)."; expire=".strftime("%d.%m.%Y %H:%M", $ch->expire_time)." -->";
			return $ch->last().$add;
		}

		$content = $ch->set($this->cacheable_body(), $this->cache_life_time());

		// Зарегистрируем сохранённый кеш в группах кеша, чтобы можно было чистить
		// при обновлении данных, от которых зависит наш контент
			
		foreach(explode(' ', $this->cache_groups()) as $group)
			if($group)
				cache_group::register($group, $this);

		return $this->stb_body = $content;
	}
		
	function cacheable_body()
	{
		$data = array();
		
		//TODO: Вычистить все _queries.
		if($qlist = $this->_queries())
		{
			if(empty($this->db) || empty($this->db->dbh))
				$this->db = &new DataBase($this->main_db_storage());
			
			foreach($qlist as $qname => $q)
			{
				$cache = false;
				if(preg_match("!^(.+)\|(\d+)$!s", $q, $m))
				{
					$q		= $m[1];
					$cache	= $m[2];
				}

				if(preg_match("/!(.+)$/s", $q, $m))
					$data[$qname] = $this->db->get($m[1], false, $cache);
				else
					$data[$qname] = $this->db->get_array($q, false, $cache);
			}
		}
		$data['template_dir'] = $this->class_dir();
		$data['this'] = $this;

		$this->template_data_fill();
		require_once('engines/smarty/assign.php');
		return template_assign_data($this->body_template(), $data);
	}

	function compiled_source() { return lcml($this->source()); }

	function _queries() { return array(); }

	function body_template_ext() { return 'html'; }

	function body_template()
	{
		if($cf = $this->class_file())
		{
			$ext = $this->body_template_ext();
			$tf = preg_replace("!\.php$!", "$1.$ext", $cf);
			if(!file_exists($tf))
				$tf = preg_replace("!\.php$!", "$1.$ext", __FILE__);
			
			return "xfile:{$tf}";
		}
		else
		{
			return 'main.html';
		}
	}

	function nav_name()
	{
		if($nav = parent::nav_name())
			return $nav;
		
		return $this->id() ? $this->class_title() : '';
	}

	var $stb_cr_type = '';
	var $stb_browser_title = '';

	function pre_show()
	{
		@header('Content-Type: text/html; charset='.config('default_character_set', 'utf-8'));
		@header('Content-Language: '.config('page_lang', 'ru'));

		if(!$this->browser_title())
			$this->set_browser_title($this->title(), false);
					
		return parent::pre_show();
	}

	var $stb_visits = 0;
	var $stb_num_replies = 0;
	
	function children_list() { return join("\n", $this->children())."\n"; }
	function set_children_list($value, $dbup) { return $this->set_children($value ? explode("\n", trim($value)) : array(), $dbup); }

	function lcml($text)
	{
		if(!$text)
			return;
	
		$ch = class_exists('Cache') ? new Cache() : NULL;
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
}
