<?php

class base_js extends base_page
{
	function use_temporary_static_file() { return false; }
	function _template_def() { return 'null.html'; }

	function pre_show()
	{
		config_set('debug.timing', false); // Чтобы не мусорить комментарием в конце JS.
		include_once("inc/js.php");
		header("Content-type: text/javascript");
		echo $this->content();
		return true;
	}

	function direct_content()
	{
		return str2js(parent::direct_content());
	}
}
