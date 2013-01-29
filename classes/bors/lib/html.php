<?php

class bors_lib_html
{
	function get_meta_data($content, $url = NULL) // via http://ru2.php.net/get_meta_tags
	{
		$content = preg_replace("'<style[^>]*>.*</style>'siU",'',$content);  // strip js
		$content = preg_replace("'<script[^>]*>.*</script>'siU",'',$content); // strip css

		$meta = array();

		foreach(explode("\n", $content) as $s)
			if(preg_match("!<link rel=\"([^\"]+)\" href=\"([^\"]+)\" />!i", trim($s), $m))
				$meta[$m[1]] = $m[2];

		$content = str_replace("\n", " ", $content);
		$content = preg_replace("!<meta !i", "\n<meta ", $content);
		$content = preg_replace("!/>!", "/>\n", $content);

		$url_data = parse_url($url);

		foreach(explode("\n", $content) as $s)
		{
			if(preg_match("!<meta[^>]+(name|property)=\"([\w:]+)\"[^>]+(content|value)=\"([^>]+)\"(.*?)>!is", trim($s), $m))
				$meta[bors_lower($m[2])] = self::norm($url_data, html_entity_decode(html_entity_decode($m[4], ENT_COMPAT, 'UTF-8'), ENT_COMPAT, 'UTF-8'), $m[2]);

			if(preg_match("!<meta[^>]+(content|value)=\"([^>]+)\"[^>]+(name|property)=\"([\w:]+)\"(.*?)>!is", trim($s), $m))
				$meta[bors_lower($m[4])] = self::norm($url_data, html_entity_decode(html_entity_decode($m[2], ENT_COMPAT, 'UTF-8'), ENT_COMPAT, 'UTF-8'), $m[4]);

			if(preg_match("!<meta[^>]+(http\-equiv|name|property)=['\"]([\w:]+)['\"][^>]+(content|value)='([^']*)'!is", trim($s), $m))
				$meta[bors_lower($m[2])] = html_entity_decode(html_entity_decode($m[4], ENT_COMPAT, 'UTF-8'), ENT_COMPAT, 'UTF-8');

			if(preg_match("!<meta[^>]+(name|property)=([\w:]+)[^>]+(content|value)=\"([^>]+)\"(.*?)>!is", trim($s), $m))
				$meta[bors_lower($m[2])] = html_entity_decode(html_entity_decode($m[4], ENT_COMPAT, 'UTF-8'), ENT_COMPAT, 'UTF-8');

			// <link rel="image_src" href="http://infox.ru/photos/2011/17/112717/300x168_IRp6fXolYdFHbUso28YKRYQS8y3fn0Ca.jpg" >
			if(preg_match("!<link [^>]*rel=['\"]?([\w:]+)['\"]?[^>]+(href)=\"(.*?)\"!is", trim($s), $m))
				$meta[bors_lower($m[1])] = self::norm($url_data, html_entity_decode(html_entity_decode($m[3], ENT_COMPAT, 'UTF-8'), ENT_COMPAT, 'UTF-8'));
		}

		if(empty($meta['title']) && preg_match('!<title>([^>]+)</title>!si', $content, $m))
			$meta['title'] = html_entity_decode($m[1], ENT_COMPAT, 'UTF-8');

		if(empty($meta['title']) && preg_match('!<h1[^>]*>([^>]+)</h1>!si', $content, $m))
			$meta['title'] = html_entity_decode($m[1], ENT_COMPAT, 'UTF-8');

		$meta['host'] = @$url_data['host'];

		return $meta;
	}

	static function norm($url_data, $value, $type = NULL)
	{
		if(!$url_data || $value[0] != '/')
			return $value;

		if($type && $type != 'og:image')
			return $value;

		return $url_data['scheme'].'://'.$url_data['host'].$value;
	}

	static function autolink($text)
	{
		$text = preg_replace('!^(www\.\S+)$!', "<a href=\"http://$1\">$1</a>", $text);
		$text = preg_replace('!^(\S+@\S+)$!', "<a href=\"mailto:$1\">$1</a>", $text);
		return $text;
	}
}
