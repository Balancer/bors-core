<?php

class render_fullpage extends base_null
{
	function render($object)
	{
	    require_once('funcs/templates/bors.php');

		if(!$object->loaded() && !$object->can_be_empty())
			return false;
			
		return template_assign_bors_object($object, 'empty.html');
	}
}
