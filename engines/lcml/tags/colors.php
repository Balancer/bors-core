<?php

foreach(explode(' ','red green blue yellow gray silver orange white black') as $color)
	eval("function lp_{$color}(\$txt)   { return \"<span style=\\\"color: {$color};\\\">\".lcml(\$txt).\"</span>\"; }");
	
function lp_color($txt, $params)
{
	if(empty($params['name']))
		$color = $params['orig'];
	else
		$color = $params['name'];
	return "<span style=\"color:{$color}\">".lcml($txt)."</span>";
}
