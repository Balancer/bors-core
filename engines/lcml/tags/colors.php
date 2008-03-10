<?
	foreach(split(' ','red green blue yellow gray silver orange white black') as $color)
		eval("function lp_$color(\$txt)   { return \"<span style=\\\"color: $color;\\\">\".lcml(\$txt).\"</span>\"; }");
	
    function lp_color($txt, $params)  { return "<span style=\"color:".@$params['name'].";\">".lcml($txt)."</span>";}
