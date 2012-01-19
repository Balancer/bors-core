<?php

function lp_indent($text, &$params)
{
	$params['skip_around_cr'] = 'full';
	return "<div style=\"padding-left: 3ex;\">".lcml(trim($text))."</div>";
}
