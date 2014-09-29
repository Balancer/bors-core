<?php

function lsp_l($txt, $params, $lcml)
{
	$html = preg_replace_callback("!^(\-|#i)\s+(.+)$!m", function($m) use($lcml) {
		return "\t<li>".$lcml->parse($m[2])."</li>";
	}, $txt);

	return save_format("\n<ul>\n{$html}\n</ul>\n");
}

// function lst_i($txt) { return "<li/>".lcml($txt)."\n";}
