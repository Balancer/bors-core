<?php

function lsp_l($txt)
{ 
	$txt = preg_replace("!^\-\s+(.+)$!me","'<li>'.lcml(stripq('$1')).'</li>\n'", $txt);
	return "\n<ul>$txt</ul>\n";
}
