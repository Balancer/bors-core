<?php

class_load('def_empty');

class render_self extends base_empty
{
	function render($object)
	{
		return $object->render();
	}
}
