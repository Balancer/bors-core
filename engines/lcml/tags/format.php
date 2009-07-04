<?php

function lp_pre($text,$params)
{
	$text = preg_replace("!^ !m","&nbsp;",$text);
	$text = preg_replace("! {2}!","&nbsp; ",$text);
	$text = preg_replace("!<br>!","\n",$text);
//	$text = str_replace("\n", "---save_cr---", $text);
	return "<pre style=\"font-size:14pt; word-wrap: break-word;\">$text</pre>\n";
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
