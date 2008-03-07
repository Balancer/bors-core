<?php

class render_page extends base_null
{
	function render($object)
	{
	    require_once('funcs/templates/bors.php');

		if(!$object->loaded() && !$object->can_be_empty())
			return false;
			
		$object->template_data_fill();
		return template_assign_bors_object($object);
	}
}
