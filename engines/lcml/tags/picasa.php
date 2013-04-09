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

//		print_d($content);

		// <meta name="description" content="26.01.2012 - Быстрые и медленные"/>
		if(preg_match('!<meta name="description" content="(.+?)"/>!', $content, $m))
			$title = $m[1];
		else
			$title = "";

		$big_thumb_url = preg_replace('!/s\d+(-d)?/!', "/s1600/", $thumb_url);
		$thumb_url = preg_replace('!/s\d+(-d)?/!', "/s$width/", $thumb_url);

//		if($params['notitle'])
//			$title = NULL;

		if($title)
			$a_title = " title=\"".htmlspecialchars(strip_tags($title))."\"";
		else
			$a_title = "";

		return "<div class=\"rs_box".($title?'':'_nd')."\" style=\"width:{$width}px\"><a href=\"$big_thumb_url\" class=\"cloud-zoom thumbnailed-image-link\" rel=\"position:'inside'\"{$a_title}><img src=\"$thumb_url\" />" . ($title ? "<br/><small class=\"inbox\">$title @ PicasaWeb</small>" : "")."</a></div>";
	}

	return "$id";
}
