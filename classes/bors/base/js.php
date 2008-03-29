<?php

class base_js extends base_page_db
{
	function can_be_empty() { return true; }

	function preShowProcess()
	{
		include_once("inc/js.php");
		header("Content-type", "text/javascript");
		return str2js($this->cacheable_body());
	}

	function storage_engine() { return ''; }
}
