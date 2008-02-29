<?php

class page_fs_separate extends base_page
{
	function storage_engine()	{ return 'storage_fs_separate'; }
	function render_engine()	{ return 'render_page'; }
	function body_engine()		{ return 'body_source'; }
	function can_be_empty()		{ return false; }

	private $_parents;
	function parents()
	{
		if($this->_parents)
			return $this->_parents;
		
		$pp = parent::parents(); 
		
		if(count($pp) == 1 && $pp[0]== $this->url())
			return array(dirname($this->url()));
		
		return $pp;
	}

	function set_parents($array)
	{
		return $this->_parents = $array;
	}
}
