<?php

class bors_forms_hidden extends bors_forms_element
{
	function html()
	{
		$params = $this->params();

		extract($params);

		$value = $this->value();

		return "<input type=\"hidden\" name=\"$name\" value=\"".htmlspecialchars($value)."\" />\n";
	}
}
