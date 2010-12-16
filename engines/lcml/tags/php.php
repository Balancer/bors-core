<?php

if(!config('geshi_include'))
	return;

/**
	[php]echo 2*2;[/php] - прямой строчный PHP-код

	При проверке на многострочность в случае исправлений проверить однострочность на
	http://balancer.ru/support/2010/12/t75934--voprosy-klassifikatsii-obektov.6737.html
*/

include_once(config('geshi_include'));

function lp_php($text, $params)
{
	$text = restore_format($text);
//	$text = html_entity_decode($text, ENT_NOQUOTES);

	$lines = count(explode($text, "\n"));
	$geshi = new GeSHi($text, NULL);
	$geshi->set_language('PHP');
	$geshi->set_encoding('UTF-8');
	$geshi->set_header_type(GESHI_HEADER_NONE);
	if($lines > 1)
	{
		$geshi->enable_classes();
		$geshi->enable_line_numbers(GESHI_FANCY_LINE_NUMBERS);
		$geshi->set_overall_class('code');
	}
	else
	{
		$geshi->enable_line_numbers(GESHI_NO_LINE_NUMBERS);
		$geshi->set_overall_class('');
	}

	$geshi->set_use_language_tab_width(true);

	$highlighted_code = $geshi->parse_code();

	return "<tt class=\"code\">{$highlighted_code}</tt>";
}
