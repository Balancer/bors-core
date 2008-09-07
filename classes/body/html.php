<?php

require_once('engines/lcml.php');

class body_html extends base_null
{
	function body($obj)
	{
		return $obj->source();
	}
}
