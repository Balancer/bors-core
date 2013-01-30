<?php

class base_page_redirect extends base_page
{
	function is_loaded() { return true; }

	function pre_show()
	{
		return go($this->args('go'));
	}
}
