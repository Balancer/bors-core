<?php

class bors_template
{
	function render_page($object)
	{
		$template = $object->template();
		$data = $object->data;

		foreach(explode(' ', $object->template_vars()) as $var)
			$data[$var] = $object->$var();

		$data['this'] = $object;

		return $this->fetch($template, $data);
	}
}
