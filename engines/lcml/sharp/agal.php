<?php

function lsp_agal($inner_text, $title)
{
	if(preg_match('/^(.+?)\|(.+)$/', $title, $m))
	{
		$title = trim($m[1]);
		$image_width = $m[2];
	}
	else
	{
		$title = trim($title);
		$image_width = 200;
	}

	config_set('lcml_tmp_agal_thumb_image_geo', is_numeric($image_width) ? "{$image_width}x" : $image_width);

	$out = "<table class=\"btab w100p\">\n";
	if($title)
		$out .= "<caption>{$title}</caption>\n";
	$out .= lcml($inner_text);
	$out .= "</table>";
	
	return $out;
}
