<?php

class base_jss extends base_page
{
	function body_template_ext() { return 'js'; }

	function pre_show()
	{
		header("Content-type", "text/javascript");
		return $this->cacheable_body();
	}
}
