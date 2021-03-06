<?php

class bors_page_fs_markdown extends \B2\Page
{
	//TODO: на время отладки
	function can_cached() { return false; }
	function can_be_empty() { return false; }

	function storage_engine() { return 'bors_storage_fs_markdown'; }

	function _access_engine_def() { return \bors_access_public::class; }

	function pre_show()
	{
		// Если путь не оканчивается на слеш, редиректим. Иначе могут быть проблемы с относительными путями.
		if(bors()->request()->url_match('![^/]$!'))
			return go($this->url().'/');

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
		$html = \Michelf\MarkdownExtra::defaultTransform($this->source());
		// Пока в Michelf\Markdown нельзя задавать классы таблиц:
		$html = str_replace("<table>", "<table class=\"{$this->layout()->table_class()}\">", $html);
		return $html;
	}

	static function __unit_test($suite)
	{
		$foo = bors_foo(__CLASS__);
		$foo->set_source('*test*', false);
		$suite->assertEquals("<p><em>test</em></p>", trim($foo->body()));
	}
}
