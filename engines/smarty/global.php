<?php

function set_global_template_vars($data) { return $GLOBALS['cms']['templates']['data'] = $data; }
function set_global_template_var($name, $value) { return $GLOBALS['cms']['templates']['data'][$name] = $value; }
function global_template_vars() { return is_array($x = @$GLOBALS['cms']['templates']['data']) ? $x : array(); }

function template_nocache()
{
	@header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
	@header("Expires: Mon, 22 Oct 1973 06:45:00 GMT"); // Date in the past
	bors_page::add_template_data_array('meta[Pragma]', 'no-cache');
	bors_page::add_template_data_array('meta[Cache-Control]', 'no-cache');
	bors_page::add_template_data_array('meta[Expires]', 'Mon, 22 Oct 1973 06:45:00 GMT');
}

function template_noindex()
{
	bors_page::add_template_data_array('meta[robots]', 'noindex, follow');
}

function template_jquery()
{
	if(bors_page::template_data('jquery_has_added'))
		return;

	bors_page::add_template_data_array('js_include', '/_bors3rdp/jquery/jquery-1.4.2.min.js');
	bors_page::add_template_data('jquery_has_added', true);
}

function template_jquery_plugin($name)
{
	if(bors_page::template_data('jquery_plugin_'.$name.'_has_added'))
		return;

	bors_page::add_template_data_array('js_include', '/_bors3rdp/jquery/plugins/'.$name);
	bors_page::add_template_data('jquery_plugin_'.$name.'_has_added', true);
}

function template_jquery_plugin_css($css)
{
	if(bors_page::template_data('jquery_plugin_'.$css.'_css_has_added'))
		return;

	bors_page::merge_template_data_array('css_list', array("/_bors3rdp/jquery/plugins/$css"));
	bors_page::add_template_data('jquery_plugin_'.$css.'_css_has_added', true);
}

function template_js($js_code)
{
	$hash = md5($js_code);
	if(bors_page::template_data('template_js_'.$hash))
		return;

	bors_page::add_template_data_array('javascript', trim($js_code));
	bors_page::add_template_data('template_js_'.$hash, true);
}

function template_js_include($js_link)
{
	$hash = md5(print_r($js_link, true));
	if(bors_page::template_data('template_js_include_'.$hash))
		return;

	bors_page::add_template_data_array('js_include', $js_link);
	bors_page::add_template_data('template_js_include_'.$hash, true);
}

function template_css($css)
{
	$hash = md5(print_r($css, true));
	if(bors_page::template_data('template_css_'.$hash))
		return;

	bors_page::merge_template_data_array('css_list', array($css));
	bors_page::add_template_data('template_css_'.$hash, true);
}

function do_php($code)
{
	eval($code);
	return $content;
}

function template_jquery_ui_tabs($id)
{
//	template_css('http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css');
	template_css('/_bors3rdp/jquery/jquery-ui.css');
	template_jquery();
	template_js_include('http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.min.js');

	template_js("jQuery(document).ready(function() { jQuery('$id').tabs(); });");
}

function template_description($text)
{
	bors_page::add_template_data_array('meta[description]', htmlspecialchars($text));
}
