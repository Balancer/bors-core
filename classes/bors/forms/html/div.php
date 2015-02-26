<?php

class bors_forms_html_div extends bors_forms_element
{
	function html()
	{
		$params = $this->params();

		extract($params);

		return "<div id=\"{$name}\"></div>";
	}
}
