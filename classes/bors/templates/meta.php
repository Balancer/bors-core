<?php

class bors_templates_meta
{
	static function append_data($name, $value)
	{
		$data = base_object::template_data($name);
		$data[] = $value;
		base_object::add_template_data($name, $data);
	}

	static function form_hidden_data($name)
	{
		if($vars = base_object::template_data($name))
			return "<input type=\"hidden\" name=\"".str_replace('form_', '', $name)."\" value=\"".join(',', array_unique(array_filter($vars)))."\" />\n";

		base_object::add_template_data($name, NULL);
	}
}
