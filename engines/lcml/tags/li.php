<?php

function lt_li($text)
{
	return "<li />";
}

function lp_li($text, &$param)
{
	$param['skip_around_cr'] = true;
	$param['inline'] = true;
	return save_format("<li>".lcml($text, array('prepare' => true))."</li>\n");
}

function lp_ul($text, &$param)
{
	if($param['orig'])
		$type = " type=\"".htmlspecialchars($param['orig'])."\"";
	else
		$type = "";

	// Комментарий убран, чтобы не было пустых полей вокруг списков.
	// Если что, обратить внимание на http://ipotek-bank.wrk.ru/services/10/kredit-zalogovyj/
	// Непонятно, зачем очистка переводов строк вокруг была убрана раньше.
	$param['skip_around_cr'] = 'one';
	return "\n".save_format("\n<ul$type>".lcml(trim($text))."</ul>\n")."\n";
}

function lp_ol($text, $param)
{
	if($param['orig'])
		$type = " type=\"".htmlspecialchars($param['orig'])."\"";
	else
		$type = "";

	$param['skip_around_cr'] = 'one';
	return save_format("\n<ol$type>".lcml($text)."</ol>\n");
}

require_once('inc/strings.php');
function lp_list($text, &$params)
{
	$params['skip_around_cr'] = 'full';

	if(@$params['list'] == 1)
		$tag = 'ol';
	else
		$tag = 'ul';

	return save_format("\n<$tag>".preg_replace('/^\[\*\](.*?)$/me', "'<li>'.lcml(stripq('$1')).'</li>'", $text)."</$tag>\n");
}
