<?php

class render_page extends base_null
{
	function render($object)
	{
//	    require_once('engines/smarty/bors.php');

		if(!$object->loaded() && !$object->can_be_empty())
			return false;

		$template_renderer = new bors_templates_smarty(NULL);
		return $template_renderer->render_page($object); //template_assign_bors_object($object, NULL, true);
	}
}
