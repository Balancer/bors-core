<?php

// Для тестов.
// Пример .hts — http://www.aviaport.ru/pages/2012/save-il-14/

class bors_page_hts extends bors_page
{
	function can_be_empty() { return false; }

	function storage_engine() { return 'bors_storage_htsu'; }

	private $parents = array();
	function parents() { return $this->parents ? $this->parents : parent::parents(); }
	function set_parents($arr, $dbup) { return $this->parents = &$arr; }

	function hts_extension() { return 'hts'; }

	function pre_show()
	{
		if($redirect = $this->get('show_redirect'))
			return go($redirect);

//		config_set('cache_disabled', true);
		config_set('lcml_markdown', true);
		return parent::pre_show();
	}
}
