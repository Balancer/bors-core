<?php
function set_global_template_var($name, $value)
{
	$GLOBALS['cms']['templates']['data'][$name] = $value;
}

class_include('base_object');

function templates_pragma_no_cache()
{
	base_object::add_template_data_array('meta[Pragma]', 'no-cache');
	base_object::add_template_data_array('meta[Cache-Control]', 'no-cache');
	base_object::add_template_data_array('meta[Expires]', 'Mon, 22 Oct 1973 06:45:00 GMT');
}

function templates_noindex()
{
	base_object::add_template_data_array('meta[robots]', 'noindex, follow');
}

function do_php($code)
{
	ob_start();
	eval($code);
	$out = ob_get_contents();
	ob_clean();
	if(preg_match("!{$_SERVER['DOCUMENT_ROOT']}/cms/funcs/templates/!", $out))
		return "$out Error in code<xmp>$code</xmp>";

	return $out;
}
