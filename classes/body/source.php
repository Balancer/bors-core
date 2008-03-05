<?php

class body_source extends base_null
{
	function body($obj)
	{
		return lcml($obj->source());
	}
}
