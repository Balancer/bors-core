<?php

function lsp_l($txt, $params)
{
	$html = preg_replace_callback("!^(\-|#i)\s+(.+)$!m", function($m) use($params) {
		return "\t<li>".$params['lcml']->parse($m[2])."</li>";
	}, $txt);

//	echo "================\n$html\n===============\n";
	return save_format("\n<ul>\n{$html}\n</ul>\n");
}

// function lst_i($txt) { return "<li/>".lcml($txt)."\n";}
