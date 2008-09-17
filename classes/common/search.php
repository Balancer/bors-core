<?php

include_once('engines/search.php');
class_include('base_page');

class common_search extends base_page
{
	function _class_file() { return __FILE__; }
	
	function title() { return ec("Поиск"); }

	function pre_parse()
	{
		unset($_GET['class_name']);
		return false;
	}

	function query() { return urldecode(@$_GET['query']); }
	function where() { return urldecode(@$_GET['where']); }
	function where_list()
	{
		return array(
			'titles' => ec("В заголовках"),
			'bodies' => ec("В сообщениях"),
		);
	}
	
	function do_query($params)
	{
		$q = $this->query();
		return $this->where() == 'bodies' ? bors_search_in_bodies($q, $params) : bors_search_in_titles($q, $params);
	}
	
	function __construct($id, $page)
	{
		parent::__construct($id);

		$this->add_template_data_array('meta[robots]', 'noindex');
	
		if(!$this->query())
			return;
	
		require_once('engines/search.php');
	
		$this->add_template_data('result',	$this->do_query(array('page' => $this->page())));
		$this->add_template_data('start',	($this->page()-1)*25+1);
	}
	
	function total_pages() { return intval(($this->total_results() - 1) / 25) + 1; }

	function total_results()
	{
		$GLOBALS['cms']['cache_disabled'] = false;
	
		$ch = &new Cache();
		if($ch->get('seacrh-total-results', $this->query().':'.$this->where()))
			return $ch->last();

		return $ch->set($this->do_query(array('pages' => true)), 86400);

	}

	function pages_links()
	{
		if($this->total_pages() < 2)
			return "";

		include_once('funcs/design/page_split.php');
		return join(" ", pages_show($this, $this->total_pages(), 20));
	}
	
	function page() { return max(1, intval(@$_GET['page'])); }

	function url($page = 1)
	{
		return "/search/?query=".urlencode($this->query())."&where={$this->where()}&page={$page}";
	}
}
