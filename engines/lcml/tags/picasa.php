<?php

function lp_picasa($id, $params)
{
	if(preg_match('!http://picasaweb.google.com/lh/photo/(\w+)$!', $id, $m))
		$id = $m[1];

	if(strlen($id) > 20) // Это фотография
	{
		$size = defval_ne($params, 'size', '640x');
		@list($width, $height) = explode('x', $size);

		require_once('inc/http.php');
		$content = http_get_content($url = "http://picasaweb.google.com/lh/photo/{$id}?feat=directlink");

		if(preg_match('!<link rel=\'image_src\' href="(.+?)"/>!', $content, $m))
			$thumb_url = $m[1];
		else
			return $url;

		if(preg_match('!<meta name="description" content="(.+?)"/>!', $content, $m))
			$title = $m[1];
		else
			$title = "";

		$thumb_url = preg_replace('!/s\d+/!', "/s$width/", $thumb_url);

		return "<a href=\"$url\"><img src=\"$thumb_url\" />" . ($title ? "<br/>\n$title" : "")."</a>";
	}

	return "$id";
}
