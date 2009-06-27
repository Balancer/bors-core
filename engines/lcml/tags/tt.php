<?php
function lp_tt($text)
{
	$text = preg_replace("!^ !m","&nbsp;",$text);
	$text = preg_replace("! {2}!","&nbsp; ",$text);
	return "<tt><span style=\"font-family:Courier New;\">".lcml($text)."</span></tt>\n";
}
