<?php

class bors_templates_bootstrap3 extends bors_object
{
	function render_class() { return 'self'; }

	function render($object)
	{
		return bors_templaters_php::fetch(__DIR__.'/bootstrap3.tpl.php', array_merge(array('self' => $object), $object->page_data()));
	}
}
