<?php

require_once('engines/lcml.php');

class body_source extends base_null
{
	function body($obj)
	{
		return $obj->lcml($obj->source());
	}
}
