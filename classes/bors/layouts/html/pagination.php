<?php

class bors_layouts_html_pagination extends bors_module
{
	function html_code()
	{
		$object = $this->args('object');

		return $object->pages_links('pages_select', $this->args('pages_title'));
	}
}
