<?php

class page_fs_xml extends base_page
{
	function storage_engine() { return 'storage_fs_xml'; }
	function render_engine(){ return 'render_page'; }
	function body_engine()	{ return 'body_source'; }
	function admin_engine()	{ return 'bors_admin_engine_page'; }

	function can_be_empty()		{ return false; }
	function class_title()		{ return ec('Страница'); }
	function class_title_rp()	{ return ec('страницы'); }
	function class_title_dp()	{ return ec('странице'); }
	function class_title_vp()	{ return ec('страницу'); }

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

	function editor_fields_list()
	{
		return array(
			ec('Полный заголовок материала:') => 'title',
			ec('Краткий заголовок материала:') => 'nav_name',
			ec('Краткое описание:') => 'description|textarea=2',
			ec('Текст:') => 'source|textarea=20',
			ec('Тип перевода строк:') => 'cr_type|dropdown=common_list_crTypes',
		);
	}

	function storage_skip_fields() { return 'storage_file url_engine'; }
}
