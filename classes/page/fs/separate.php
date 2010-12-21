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

	function children()
	{
		return $this->children_ex($this->url(), $this->storage_base_dir(), $this->storage_file_prefix());
	}

	function children_ex($url, $base, $pfx)
	{
		$ch = new Cache();
		if($ch->get('page-fs-separate-children-cache', "$url:$base:$pfx"))
			return $ch->last();

		$dh = opendir($base);
		$children = array();
		while(($file = readdir($dh)) !== false)
		{
			$subdir = $base.$file.'/';
			$subfile = "{$subdir}{$pfx}title.txt";
			if($file{0} != '.' && is_dir($subdir) && file_exists($subfile))
				$children[] = bors_load_uri($url.$file.'/');
//			echo "$subfile<br/>\n";
		}
		closedir($dh);
		return $ch->set($children, 3600);
	}

	function search_weight() //TODO: жёсткий харкод. Убрать потом в РП.
	{
		if(preg_match('!/action/!', $this->url()))
			return 100000000;

		if(preg_match('!/contacts/!', $this->url()))
			return 1000000;

		if(preg_match('!http://[^/]+/\w+/$!', $this->url()))
			return 100;

		return 10;
	}
}
