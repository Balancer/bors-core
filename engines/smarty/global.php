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

function template_jquery($link = NULL)
{
	if(bors_page::template_data('jquery_has_added'))
		return;

	if(!$link)
		$link = '/_bors3rdp/jquery/jquery-1.4.2.min.js';

	bors_page::add_template_data_array('js_include', $link);
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

function template_style($style)
{
	$hash = md5(print_r($style, true));
	if(bors_page::template_data('template_style_'.$hash))
		return;

	bors_page::merge_template_data_array('style', array($style));
	bors_page::add_template_data('template_style_'.$hash, true);
}

function do_php($code)
{
	eval($code);
	return $content;
}

function template_jquery_ui()
{
	template_jquery();
//	template_js_include('http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.min.js');
	template_js_include('/_bors3rdp/jquery/ui/jquery-ui-1.8.5.custom.min.js');
}

function template_jquery_ui_css($tpl = 'overcast') { template_css('/_bors3rdp/jquery/ui/themes/'.$tpl.'/jquery-ui-1.8.5.custom.css'); }

function template_jquery_ui_tabs($id)
{
	template_jquery_ui();
	template_jquery_ui_css();

	template_js("jQuery(document).ready(function() { jQuery('$id').tabs(); });");
}

function template_jquery_ui_datapicker($lang = 'ru')
{
	template_jquery_ui();
	template_jquery_ui_css();

	template_js_include('/_bors3rdp/jquery/ui/jquery.ui.datepicker-'.$lang.'.js');
}

function template_jquery_ui_autocomplete($id, $url)
{
	template_jquery_ui();
	template_jquery_ui_css();
	template_js("jQuery(document).ready(function() { jQuery('$id').tabs(); });");
}

function template_description($text)
{
	bors_page::add_template_data_array('meta[description]', htmlspecialchars($text));
}

function template_jquery_markitup($id)
{
	template_jquery();
	$base = config('jquery.markitup.base');

	template_css("/_bors3rdp/jquery/plugins/$base/skins/simple/style.css");
	template_css("/_bors3rdp/jquery/plugins/".config('jquery.markitup.sets.bbcode')."/style.css");

	template_js_include("/_bors3rdp/jquery/plugins/$base/jquery.markitup.js");
	template_js_include("/_bors3rdp/jquery/plugins/".config('jquery.markitup.sets.bbcode')."/set.js");

	template_js("jQuery(document).ready(function() { jQuery('$id').markItUp(mySettings); });");

//	jQuery('#bbcode').height(300);
}

function template_rightjs() { template_js_include('/_bors3rdp/rightjs/right-safe.js'); }
function template_rightjs_plugin($name) { template_rightjs(); template_js_include("/_bors3rdp/rightjs/right-{$name}.js"); }

function template_meta_prop($name, $value)
{
	bors_page::add_template_data_array('head_append', "<meta property=\"{$name}\" content=\"".htmlspecialchars($value)."\"/>");
}
