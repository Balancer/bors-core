<?php

function lp_size($txt, $params)
{
	extract($params);
	if(empty($size))
		$size = $orig;

	$size = trim($size);

	if(is_numeric($size))
	{
		if($size > 30)
			$size .= "%";
		else
			$size .= "pt";
	}

	return "<div style=\"font-size: ".addslashes($size).";\">".lcml($txt)."</div>";
}
