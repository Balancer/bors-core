<?php

class bors_templates_uikit extends bors_object
{
	function render_class() { return 'self'; }

	function render($object)
	{
		return bors_templaters_php::fetch(__DIR__.'/uikit.tpl.php', array_merge(array('self' => $object), $object->page_data()));
	}
}
