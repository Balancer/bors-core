<?php

class bors_page_fs_markdown extends base_page
{
	//TODO: на время отладки
	function can_cached() { return false; }
	function can_be_empty() { return false; }

	function storage_engine() { return 'bors_storage_fs_markdown'; }

	function pre_show()
	{
		config_set('cache_disabled', true);
		return parent::pre_show();
	}

//	function cache_static() { return rand(10*86400, 30*86400); }

	function template()
	{
		$this->add_template_data('skip_page_title', true);
		$this->add_template_data('skip_page_admin', true);

		return parent::template();
	}

	function body()
	{
		require_once(config('markdown_include'));
		return Markdown($this->source());
	}
}
