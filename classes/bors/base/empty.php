<?php

class base_empty extends base_null
{
	var $id;
	var $initial_id = NULL;

	function id() { return $this->id; }
	function set_id($id) { $this->id = $id; }
	
	function __construct($id)
	{
		$this->id = $this->initial_id = $id;
//		$this->page = $this->default_page();
	}

	function storage_engine() { return ''; }
	var $stb_body_engine = '';
	function loaded() { return true; }
	function internal_uri() { return get_class($this).'://'.$this->id(); }
	function cache_clean() { }

	function auto_search_index() { return false; }

	private $_is_sleeping = false;
	function sleep()
	{
		if($this->_is_sleeping) 
			return;

		$this->_is_sleeping = true;

		foreach(get_object_vars($this) as $k => $v)
			if(is_object($v) && method_exists($v, 'sleep'))
				$v->sleep();
	}
	
	function wakeup()
	{
		if(!$this->_is_sleeping) 
			return;

		$this->_is_sleeping = false;

		foreach(get_object_vars($this) as $k => $v)
			if(is_object($v) && method_exists($v, 'wakeup'))
				$v->wakeup();
	}
}
