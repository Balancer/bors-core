<?php

require_once('engines/lcml/main.php');

class body_html extends base_null
{
	function body($obj)
	{
		return $obj->source();
	}
}
