<?php

class base_empty extends base_null
{
	var $id;
	var $initial_id = NULL;

	function id() { return $this->id; }
	function set_id($id) { $this->id = $id; }
	
	function __construct($id)
	{
		$this->page = $this->default_page();
		$this->id = $this->initial_id = $id;
	}

	var $page;
	function page() { return $this->page; }
	//TODO: со временем - убрать!
	function set_page($page)
	{
		if(!$page && $this->default_page())
			$this->page = $this->default_page();
		else
			$this->page = $page;
	}

	function default_page() { return 1; }
	function storage_engine() { return ''; }
	function body_engine() { return ''; }
	function loaded() { return true; }
	function internal_uri() { return get_class($this).'://'.$this->id(); }
	function cache_clean() { }

	function auto_search_index() { return false; }
}
