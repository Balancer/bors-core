<?php

class page_fs_separate extends base_page
{
	function storage_engine()	{ return 'storage_fs_separate'; }
	function render_engine()	{ return 'render_page'; }
	var $stb_body_engine = 'body_source';
	function can_be_empty()		{ return false; }
	function can_cached()		{ return false; }

	var $_parents;
	function parents() { return $this->_parents ? $this->_parents : parent::parents(); }
	function set_parents($array) { return $this->_parents = $array; }

//	function url($page=1) { return object_load($this->url_engine(), $this)->url($page); }

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

	function autofields() { return 'cr_type'; }
}
