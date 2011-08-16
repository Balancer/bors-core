<?php

function lp_picasa($id, $params)
{
	if(preg_match('!https?://picasaweb.google.com/lh/photo/(\w+)$!', $id, $m))
		$id = $m[1];

	if(strlen($id) > 20) // Это фотография
	{
		$size = defval_ne($params, 'size', '640x');
		@list($width, $height) = explode('x', $size);

		$url = "http://picasaweb.google.com/lh/photo/{$id}?feat=directlink";
		$cache_status_save = config('cache_disabled');
		config_set('cache_disabled', false);
		$ch = new Cache;
		if($ch->get('lcml-tags-picasa', 'page-v2-'.$url))
		{
			$content = $ch->last();
		}
		else
		{
			require_once('inc/http.php');
			$content = http_get_content($url);
			$ch->set($content, 3600);
		}
		config_set('cache_disabled', $cache_status_save);

		if(preg_match('!<link rel=\'image_src\' href="(.+?)"/>!', $content, $m))
			$thumb_url = $m[1];
		else
		{
			debug_hidden_log('external_code', 'picasa: can not find image '.$url." in \n".$content);
			return "<a href=\"$url\">$url</a>";
		}

		if(preg_match('!<meta name="description" content="(.+?)"/>!', $content, $m))
			$title = $m[1];
		else
			$title = "";

		$thumb_url = preg_replace('!/s\d+/!', "/s$width/", $thumb_url);

		if($params['notitle'])
			$title = NULL;

		return "<a href=\"$url\"><img src=\"$thumb_url\" />" . ($title ? "<br/>\n$title" : "")."</a>";
	}

	return "$id";
}
