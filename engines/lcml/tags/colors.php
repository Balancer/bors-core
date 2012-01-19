<?php

foreach(explode(' ','red green blue yellow gray silver orange white black') as $color)
	eval("function lp_{$color}(\$txt)   { return \"<span style=\\\"color: {$color};\\\">\".lcml(\$txt).\"</span>\"; }");

function lp_color($txt, $params)
{
	$color = @$params['color'];

	if(!$color)
		$color = @$params['name'];

	if(!$color)
		$color = $params['orig'];

	return "<span style=\"color:{$color}\">".lcml($txt)."</span>";
}
