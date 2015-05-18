<?php

class bors_forms_js_vars extends bors_forms_element
{
	function is_hidden() { return true; }

	function html()
	{
		$params = $this->params();

		extract($params);

		return "<script>top.{$name}=".json_encode($value)."</script>";
	}
}
