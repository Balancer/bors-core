<?php

class page_fs_separate extends base_page
{
	function storage_engine()	{ return 'storage_fs_separate'; }
	function render_engine()	{ return config('render_engine', 'render_page'); }
	var $stb_body_engine = 'body_source';
	function can_be_empty()		{ return false; }
	function can_cached()		{ return false; }

	var $_parents;
	function parents() { return $this->_parents ? $this->_parents : parent::parents(); }
	function set_parents($array) { return $this->_parents = $array; }

	function url($page=NULL) { return ($u=parent::url($page)) ? $u : $this->id(); }

	function autofields() { return 'cr_type'; }

	function storage_skip_fields() { return 'class_name go id new_object_class object parent_object_uri parents_string uri'; }

	function delete() { $this->storage()->delete($this); }
}
