<?php

class bors_themes_uikit extends bors_object
{
	function render_class() { return 'self'; }

	function render($object)
	{
//		if(!$object->get('layout_class'))
			$object->set_attr('layout_class', 'bors_layouts_uikit');

		return bors_templaters_php::fetch(__DIR__.'/uikit.tpl.php', array_merge(array('self' => $object), $object->page_data()));
	}
}
