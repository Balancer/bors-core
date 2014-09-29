<?php

// http://ehkoo.github.io/semantic-ui-examples/

class bors_themes_semanticui extends bors_object
{
	function render_class() { return 'self'; }

	function render($object)
	{
//		if(!$object->get('layout_class'))
			$object->set_attr('layout_class', 'bors_layouts_semanticui');

		return bors_templaters_php::fetch(__DIR__.'/semanticui.tpl.php', array_merge(array('self' => $object), $object->page_data()));
	}
}
