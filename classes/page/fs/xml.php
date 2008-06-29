<?php

class page_fs_xml extends base_page
{
	function storage_engine()	{ return 'storage_fs_xml'; }
	function render_engine()	{ return 'render_page'; }
	function body_engine()		{ return 'body_source'; }
	function can_be_empty()		{ return false; }

	var $_parents;
	function parents() { return $this->_parents ? $this->_parents : parent::parents(); }
	function set_parents($array) { return $this->_parents = $array; }

	private $_children;
	function children() { return $this->_children ? $this->_children : parent::children(); }
	function set_children($array) { return $this->_children = $array; }

	function __construct($id)
	{
		if(preg_match('!^(/.+\.xml)/$!', $id, $m))
			$id = "http://{$_SERVER['HTTP_HOST']}{$m[1]}";
		
		parent::__construct($id);
	}

	function relative_path()
	{
		return preg_replace('!/index\.html$!', '/', preg_replace('!\.xml$!', '.html', $this->id()));
	}

	function url($page = NULL)
	{
		$parent = parent::url($page);
		if($parent)
			return $parent;
			
		return $this->relative_path();
	}
	
	private $_storage;
	function storage()
	{
		if(!$this->_storage)
		{
			$this->_storage = object_load($this->storage_engine());
			if(!$this->_storage)
				debug_exit("Can't load storage engine '{$this->storage_engine()}' in ".join(",<br/>\n", bors_dirs()));
		}
		
		return $this->_storage;
	}
	
	function delete() { $this->storage()->delete($this); }
	function new_instance($id) { $this->set_id($id); }
}
