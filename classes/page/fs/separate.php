<?php

class page_fs_separate extends base_page
{
	function storage_engine()	{ return 'storage_fs_separate'; }
	function can_be_empty()		{ return false; }
	function can_cached()		{ return false; }

	function cache_static()		{ return config('page_fs_separate_cache_static'); }

	function attr_preset()
	{
		return array_merge(parent::attr_preset(), array(
			'body_engine'	=> 'body_source',
			'render_engine'	=> config('render_engine', 'render_page'),
		));
	}

//	var $_parents;
//	function parents() { return $this->_parents ? $this->_parents : parent::parents(); }
//	function set_parents($array) { return $this->_parents = $array; }

	function url($page=NULL) { return ($u=parent::url($page)) ? $u : $this->id(); }

//	function dont_check_fields() { return array_merge(parent::dont_check_fields, array('cr_type')); }

	function delete() { $this->storage()->delete($this); }
}
