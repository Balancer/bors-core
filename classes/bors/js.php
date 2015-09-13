<?php

class bors_js extends bors_page
{
	function body_template_ext() { return 'js'; }
	function use_temporary_static_file() { return false; }
	function _template_def() { return 'null.html'; }

	function pre_show()
	{
		$parent = parent::pre_show();

		// Маскировано, потому что может вызываться из cli
		@header('Content-type: text/javascript; charset='.$this->output_charset());
		config_set('debug.timing', false); // Чтобы не мусорить комментарием в конце JS.
		return $parent;
	}

	function body_data()
	{
		return array_merge(parent::body_data(), array(
			'smarty_auto_literal' => true,
		));
	}
}
