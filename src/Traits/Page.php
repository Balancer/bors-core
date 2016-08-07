<?php

namespace B2\Traits;

trait Page
{
	function _layout_class_def()
	{
		return object_property($this->b2_theme(), 'layout_class', 'bors_layouts_bors');
	}

	function _layout_def()
	{
		$class_name = $this->layout_class();
		$layout = new $class_name($this);
		$this->set_attr('layout', $layout);
		return $layout;
	}

	function _b2_theme_def()
	{
		return bors_load($this->get('theme_class'), NULL);
	}

	function _css_includes_def() { return \bors_page::template_data('css_list'); }
}
