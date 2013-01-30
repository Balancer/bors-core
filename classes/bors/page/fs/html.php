<?php

class bors_page_fs_html extends bors_page
{
	//TODO: на время отладки
	function can_cached() { return false; }
	function can_be_empty() { return false; }

	function storage_engine() { return 'bors_storage_fs_html'; }

	private $parents = array();
	function parents() { return $this->parents ? $this->parents : parent::parents(); }
	function set_parents($arr, $dbup) { return $this->parents = &$arr; }

	function pre_show()
	{
		if(preg_match('/^(.+)\.phtml$/', $this->called_url(), $m))
			return go($m[1].'/', true);

		config_set('cache_disabled', true);
		return parent::pre_show();
	}

	function body() { return $this->source(); }
}
