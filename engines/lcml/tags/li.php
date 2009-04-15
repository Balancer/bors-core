<?php

function lt_li($text)
{
	return "<li />";
}

function lp_li($text)
{
	return "<li>".lcml($text)."</li>";
}

function lp_ul($text, &$param)
{
	if($param['orig'])
		$type = " type=\"".htmlspecialchars($param['orig'])."\"";
	else
		$type = "";

	$param['skip_around_cr'] = true;
	return "<ul$type>".lcml($text)."</ul>\n";
}

function lp_ol($text, $param)
{
	if($param['orig'])
		$type = " type=\"".htmlspecialchars($param['orig'])."\"";
	else
		$type = "";

	return "<ol$type>".lcml($text)."</ol>\n";
}

require_once('inc/strings.php');
function lp_list($text)
{
	return save_format(preg_replace('/^\[\*\](.*?)$/me', "'<li>'.lcml(stripq('$1')).'</li>'", $text));
}
