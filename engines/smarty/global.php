<?php

function set_global_template_vars($data) { return $GLOBALS['cms']['templates']['data'] = $data; }
function set_global_template_var($name, $value) { return $GLOBALS['cms']['templates']['data'][$name] = $value; }
function global_template_vars() { return is_array($x = @$GLOBALS['cms']['templates']['data']) ? $x : array(); }

function template_nocache()
{
	@header("Cache-Control: no-cache, no-store, must-revalidate, max-age=0"); // HTTP/1.1
//	@header("Expires: Mon, 22 Oct 1973 06:45:00 GMT"); // Date in the past
	@header("Expires: 0"); // Date in the past
	@header("X-Accel-Expires: 0"); // Не кешировать в nginx
	bors_page::add_template_data_array('meta[Pragma]', 'no-cache, no-store');
//	bors_page::add_template_data_array('meta[Cache-Control]', 'max-age=0');
	bors_page::add_template_data_array('meta[Cache-Control]', 'no-store, no-cache, must-revalidate');
//	bors_page::add_template_data_array('meta[Expires]', '0');
	bors_page::add_template_data_array('meta[Expires]', 'Mon, 22 Oct 1973 06:45:00 GMT');
}

function template_noindex()
{
	bors_page::add_template_data_array('meta[robots]', 'noindex, follow');
}

function template_js_include($js_link, $prepend = false)
{
	$hash = md5(print_r($js_link, true));
	if(bors_page::template_data('template_js_include_'.$hash))
		return;

//	bors_page::add_template_data_array('js_include', $js_link);
	if($prepend)
		bors_page::prepend_template_data_array('js_include', array($js_link));
	else
		bors_page::merge_template_data_array('js_include', array($js_link));

	bors_page::add_template_data('template_js_include_'.$hash, true);
}

function template_js_include_post($js_link)
{
	$hash = md5(print_r($js_link, true));
	if(bors_page::template_data('template_js_include_'.$hash))
		return;

	bors_page::add_template_data_array('js_include_post', $js_link);
	bors_page::add_template_data('template_js_include_'.$hash, true);
}

function template_css($css, $prepend = false)
{
	$hash = md5(print_r($css, true));
	if(bors_page::template_data('template_css_'.$hash))
		return;

	if($prepend)
		bors_page::prepend_template_data_array('css_list', array($css));
	else
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

function template_meta_prop($name, $value)
{
	bors_page::add_template_data_array('head_append', "<meta property=\"{$name}\" content=\"".htmlspecialchars($value)."\"/>");
}

function template_rss($rss_url, $title)
{
	bors_page::add_template_data_array('head_append',
		"<link rel=\"alternate\" type=\"application/rss+xml\" href=\""
		.htmlspecialchars($rss_url)
		."\" title=\"".htmlspecialchars($title)."\" />");
}
