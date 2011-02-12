<?php

function lp_qr($text, $params)
{
	$width  = defval_ne($params, 'width',  150);
	$height = defval_ne($params, 'height', 150);
	$image_link = "http://chart.apis.google.com/chart?cht=qr&chs={$width}x{$height}&chld=M|0&choe=UTF-8&chl=".urlencode($text);
	return "<img src=\"{$image_link}\" width=\"{$width}\" height=\"{$height}\" />";
}
