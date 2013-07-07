<?php

class page_fs_xml extends base_page
{
	function storage_engine() { return 'storage_fs_xml'; }
	function render_engine() { return config('render_engine', 'render_page'); }
	function body_engine()	{ return 'body_source'; }
	function admin_engine()	{ return 'bors_admin_engine_page'; }

	function can_be_empty()		{ return false; }

	static function id_prepare($id)
	{
		if(preg_match('!^(/.+\.xml)/$!', $id, $m))
			$id = "http://{$_SERVER['HTTP_HOST']}{$m[1]}";

		//TODO: сделать отброс также \r
		return trim($id);
	}

	function relative_path()
	{
		return preg_replace('!/index\.html$!', '/', preg_replace('!\.xml$!', '.html', $this->id()));
	}

	function url_ex($page)
	{
		$parent = parent::url_ex($page);
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

	function delete()
	{
		$this->storage()->delete($this);
		return parent::delete();
	}

	function new_instance()
	{
		$this->storage()->save($this);
	}
}
