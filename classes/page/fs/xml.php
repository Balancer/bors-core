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
}
