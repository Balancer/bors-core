<?php

require_once('engines/lcml/main.php');

class body_source extends base_null
{
	function body($obj)
	{
		return $obj->lcml($obj->source());
	}
}
