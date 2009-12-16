<?php

function lp_q($txt, $params) { return lp_quote($txt, $params); }

function lp_quote($txt, $params)
{
	if(empty($params['description']))
		$out = " <blockquote>";
	else
		$out = " <blockquote><small><b><div class=\"quotetop\" style=\"border-bottom-width: 1px; border-bottom-style: solid;\">{$params['description']}</div></b></small>";

	if(empty($params['skip_markup']))
		return $out.'<div>'.lcml(trim($txt)).'</div></blockquote> ';
	else
		return $out.'<div>'.str_replace("\n", "<br/>\n", $txt).'</div></blockquote> ';
}
