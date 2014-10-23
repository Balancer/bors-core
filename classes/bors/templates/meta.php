<?php

class bors_templates_meta
{
	static function append_data($name, $value)
	{
		$data = bors_object::template_data($name);
		$data[] = $value;
		bors_object::add_template_data($name, $data);
	}

	static function form_hidden_data($name)
	{
		if($vars = bors_object::template_data($name))
			return "<input type=\"hidden\" name=\"".str_replace('form_', '', $name)."\" value=\"".join(',', array_unique(array_filter($vars)))."\" />\n";

		bors_object::add_template_data($name, NULL);
	}
}
