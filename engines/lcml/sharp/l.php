<?php

function lsp_l($txt)
{ 
	$txt = preg_replace_callback("!^\-\s+(.+)$!m", function($m) { return '<li>'.lcml(stripq($m[1])).'</li>\n';}, $txt);
	return "\n<ul>$txt</ul>\n";
}
