<?php

class base_empty extends base_null
{
	private $_id;
	private $_initial_id = NULL;

	function id() { return $this->_id; }
	function set_id($id) { return $this->_id = $id; }
	
	function __construct($id)
	{
		$this->set_id($this->initial_id = $id);
	}

	function storage_engine() { return ''; }
	var $stb_body_engine = '';
	function loaded() { return true; }
	function internal_uri() { return get_class($this).'://'.$this->id(); }
	function cache_clean() { }

	function auto_search_index() { return false; }
}
