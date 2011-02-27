<?php

function lp_html_div($inner, &$params)
{
	$params['skip_around_cr'] = true;
	return "<div ".make_enabled_params($params, 'id').">".lcml_h($inner)."</div>";
}
