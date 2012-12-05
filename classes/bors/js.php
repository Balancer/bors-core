<?php

class bors_js extends base_page
{
	function body_template_ext() { return 'js'; }
	function use_temporary_static_file() { return false; }
	function _template_def() { return 'null.html'; }

	function pre_show()
	{
//		header("Content-type: text/javascript");
		header('Content-type: text/javascript; charset='.$this->output_charset());
		config_set('debug.timing', false); // Чтобы не мусорить комментарием в конце JS.
		return false;
	}

	function body_data()
	{
		return array_merge(parent::body_data(), array(
			'smarty_auto_literal' => true,
		));
	}

//	function direct_content()
//	{
//		return $this->content();
//
//	}
}
