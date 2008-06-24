<?php

class base_jss extends base_page
{
	function body_template_ext() { return 'js'; }

	function pre_show()
	{
		header("Content-type", "text/javascript");
		config_set('debug_timing', false); // Чтобы не мусорить комментарием в конце JS.
		return $this->cacheable_body();
	}
}
