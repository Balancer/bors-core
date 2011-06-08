<?php

function lp_size($txt,$params)
{
	$size = trim($params['orig']);
	if($size == "".intval($size))
	{
		if($size > 30)
			$size .= "%";
		else
			$size .= "pt";
	}

	return "<div style=\"font-size: ".addslashes($size).";\">".lcml($txt)."</div>";
}
