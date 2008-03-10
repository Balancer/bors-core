<?php

class page_fs_separate extends base_page
{
	function storage_engine()	{ return 'storage_fs_separate'; }
	function render_engine()	{ return 'render_page'; }
	function body_engine()		{ return 'body_source'; }
	function can_be_empty()		{ return false; }

	var $_parents;
	function parents() { return $this->_parents ? $this->_parents : parent::parents(); }
	function set_parents($array) { return $this->_parents = $array; }

//	function url($page=1) { return object_load($this->url_engine(), $this)->url($page); }
}
