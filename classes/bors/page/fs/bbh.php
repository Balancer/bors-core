<?php

class bors_page_fs_bbh extends base_page
{
	//TODO: на время отладки
	function can_cached() { return false; }
	function can_be_empty() { return false; }

	function storage_engine() { return 'bors_storage_fs_bbh'; }

	function pre_show()
	{
		config_set('cache_disabled', true);
		config_set('lcml_markdown', true);
		return parent::pre_show();
	}

	function body()
	{
		require_once('engines/lcml/main.php');
		return lcml_bbh($this->source());
	}

	function nav_name() { return bors_lower(bors_truncate($this->title(), 30)); }
}
