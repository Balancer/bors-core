<?php

class base_jss extends base_page
{
	function body_template_ext() { return 'js'; }
	function use_temporary_static_file() { return false; }
	function template() { return 'null.html'; }

	function pre_show()
	{
		header("Content-type: text/javascript");
		config_set('debug.timing', false); // Чтобы не мусорить комментарием в конце JS.
		echo $this->content();
		return true;
	}
}
