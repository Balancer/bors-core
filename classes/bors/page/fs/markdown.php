<?php

class bors_page_fs_markdown extends bors_page
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

	function _template_def()
	{
		$this->add_template_data('skip_page_title', true);
		$this->add_template_data('skip_page_admin', true);

		return parent::_template_def();
	}

	function body()
	{
		require_once(config('markdown_include'));
		return \Michelf\Markdown::defaultTransform($this->source());
	}

	static function __unit_test($suite)
	{
		$foo = bors_foo(__CLASS__);
		$foo->set_source('*test*', false);
		$suite->assertEquals("<p><em>test</em></p>", trim($foo->body()));
	}
}
