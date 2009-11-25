<?php
/* Поддержка старого *.hts формата файлов в utf-8. */

class bors_page_fs_htsu extends base_page
{
	//TODO: на время отладки
	function can_cached() { return false; }
	function can_be_empty() { return false; }

	function storage_engine() { return 'storage_fs_htsu'; }

	private $parents = array();
	function parents() { return $this->parents ? $this->parents : parent::parents(); }
	function set_parents($arr, $dbup) { return $this->parents = &$arr; }

	function init()
	{
		if(preg_match('/^(.+)\.phtml$/', $this->called_url(), $m))
			go($m[1].'/', true);

		return parent::init();
	}

	function pre_show()
	{
		config_set('cache_disabled', true);
		return parent::pre_show();
	}

//	function cache_static() { return rand(10*86400, 30*86400); }
}
