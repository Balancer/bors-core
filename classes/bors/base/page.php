<?php

class base_page extends base_object
{
	function render_engine() { return 'render_page'; }
	function can_be_empty() { return true; }
	
	var $stb_source = NULL;
	function set_source($source, $db_update) { $this->set("source", $source, $db_update); }
	function source() { return $this->stb_source; }

	function items_around_page() { return 8; }

	function pages_links($css='pages_select')
	{
		include_once("funcs/design/page_split.php");
		$pages = '<li>'.join('</li><li>', pages_show($this, $this->total_pages(), $this->items_around_page())).'</li>';

		if($this->total_pages() > 1)
			return '<div class="'.$css.ec('"><ul><li>Страницы:</li>').$pages.'</ul></div>';
		else
			return '';
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

	function total_pages() { return  intval(($this->total_items() - 1)/$this->items_per_page()) + 1; }

	function items_per_page() { return 25; }

	function body()
	{
		if($body_engine = $this->body_engine())
		{
			$be = class_load($body_engine);
			if(!$be)
				debug_exit("Can't load body engine {$body_engine} for class {$this}");
			return $be->body($this);
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
			
		if($ch->get('bors-cached-body-v17', $this->internal_uri()) && !$drop_cache)
		{
			$add = "\n<!-- cached; create=".strftime("%d.%m.%Y %H:%M", $ch->create_time)."; expire=".strftime("%d.%m.%Y %H:%M", $ch->expire_time)." -->";
			return $ch->last().$add;
		}

		$content = $ch->set($this->cacheable_body(), $this->cache_life_time());

		// Зарегистрируем сохранённый кеш в группах кеша, чтобы можно было чистить
		// при обновлении данных, от которых зависит наш контент
			
		foreach(split(' ', $this->cache_groups()) as $group)
			if($group)
				class_load('cache_group', $group)->register($this);

		return $content;
	}
		
	function cacheable_body()
	{
		$data = array();
		
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
		require_once('funcs/templates/assign.php');
		return template_assign_data($this->body_template(), $data);
	}

	function _queries() { return array(); }

	function body_template()
	{
		if($cf = $this->class_file())
			return preg_replace("!^(.+)\.php$!", "xfile:$1.html", $cf);
		else
			return 'main.html';
	}

	function nav_name()
	{
		if($nav = parent::nav_name())
			return $nav;
		
		return $this->class_title();
	}
}
