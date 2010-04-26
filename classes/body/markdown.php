<?php

class body_markdown extends base_null
{
	function body($obj)
	{
		return bors_markup_markdown::parse($obj->source());
	}
}
