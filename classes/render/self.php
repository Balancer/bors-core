<?php

class render_self extends base_empty
{
	function render($object)
	{
		return $object->content();
	}
}
