<?php

function lp_indent($text, $params)
{
	$params['skip_around_cr'] = true;
	return "<div style=\"padding-left: 3ex;\">".lcml($text)."</div>";
}
