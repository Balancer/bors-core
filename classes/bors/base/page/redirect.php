<?php

class base_page_redirect extends base_page
{
	function loaded() { return false; }

	function init()
	{
		go($this->args('go'));
	}
}
