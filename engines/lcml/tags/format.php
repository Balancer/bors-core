<?php

function lp_pre($text, &$params)
{
	$text = preg_replace("!^ !m","&nbsp;",$text);
	$text = preg_replace("! {2}!","&nbsp; ",$text);
	$text = preg_replace("!<br>!","\n",$text);
	$params['skip_around_cr'] = true;
	return "\n<pre>$text</pre>\n";
}

function lp_xmp($text, &$params)
{
	$params['skip_around_cr'] = true;
	return "\n<xmp>$text</xmp>\n";
}

function lp_cr_as_br($text) { return preg_replace("!\n!", " <br>\n", $text); }

function lp_p($text, $params)
{
	return "<p ".make_enabled_params($params, 'style').">".lcml($text)."</p>\n";
}

function lt_p($params)
{
	return "<p ".make_enabled_params($params, 'style')." />";
}
