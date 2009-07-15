<?php
function set_global_template_vars($data) { return $GLOBALS['cms']['templates']['data'] = $data; }
function set_global_template_var($name, $value) { return $GLOBALS['cms']['templates']['data'][$name] = $value; }
function global_template_vars() { return is_array($x = @$GLOBALS['cms']['templates']['data']) ? $x : array(); }

class_include('base_object');

function templates_nocache()
{
	@header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
	@header("Expires: Mon, 22 Oct 1973 06:45:00 GMT"); // Date in the past
	base_object::add_template_data_array('meta[Pragma]', 'no-cache');
	base_object::add_template_data_array('meta[Cache-Control]', 'no-cache');
	base_object::add_template_data_array('meta[Expires]', 'Mon, 22 Oct 1973 06:45:00 GMT');
}

function templates_noindex()
{
	base_object::add_template_data_array('meta[robots]', 'noindex, follow');
}

function templates_jquery()
{
	base_object::add_template_data_array('js_include', '/_bors/js/jquery.js');
	base_object::add_template_data('jquery_has_added', true);
}
function templates_jquery_plugin($name) { base_object::add_template_data_array('js_include', '/_bors3rdp/jquery/plugins/'.$name); }

function do_php($code)
{
	eval($code);
	return $content;
}
