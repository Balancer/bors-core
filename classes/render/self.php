<?php

class render_self extends bors_object_simple
{
	function render($object)
	{
		return $object->content();
	}
}
