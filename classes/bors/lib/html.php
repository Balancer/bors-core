<?php

class bors_lib_html
{
	static function get_meta_data($content, $url = NULL) // via http://ru2.php.net/get_meta_tags
	{
		$meta = array();

		$content = preg_replace('!<meta[^>]+Content-Type[^>]+>!i', '', $content);
		$content = preg_replace('!<meta[^>]+charset[^>]+>!i', '', $content);

		$dom = new DOMDocument('1.0', 'UTF-8');
		$dom->encoding = 'UTF-8';

		@$dom->loadHTML('<?xml encoding="UTF-8">' . $content);

		foreach($dom->getElementsByTagName('meta') as $m)
		{
			$property = $m->getAttribute('property');
			if(!$property)
				$property = $m->getAttribute('name');

	 		$val  = trim($m->getAttribute('content'));
			if(!$val)
		 		$val  = trim($m->getAttribute('value'));

			if($property)
		    	$meta[$property] = $val;
	    }

		$content = preg_replace("'<style[^>]*>.*</style>'siU",  '', $content); // strip js
		$content = preg_replace("'<script[^>]*>.*</script>'siU",'', $content); // strip css

		foreach(explode("\n", $content) as $s)
			if(preg_match("!<link rel=\"([^\"]+)\" href=\"([^\"]+)\" />!i", trim($s), $m))
				$meta[$m[1]] = trim($m[2]);

		$content = str_replace("\n", " ", $content);
		$content = preg_replace("!<meta !i", "\n<meta ", $content);
		$content = preg_replace("!/>!", "/>\n", $content);

		$url_data = parse_url($url);

		foreach(explode("\n", $content) as $s)
		{
			$s = trim($s);
			if(!$s)
				continue;

			if(preg_match("!<meta[^>]+(http\-equiv|name|property)='([\w:]+)'[^>]+(content|value)='([^']*)'!is", $s, $m))
				$meta[bors_lower($m[2])] = self::decode($m[4]);
			elseif(preg_match('!<meta[^>]+(http\-equiv|name|property)="([\w:]+)"[^>]+(content|value)="([^"]*)"!is', $s, $m))
				$meta[bors_lower($m[2])] = self::decode($m[4]);
			elseif(preg_match("!<meta[^>]+(http\-equiv|name|property)=(\S+)[^>]+(content|value)=([^\s>])+!is", $s, $m))
				$meta[bors_lower($m[2])] = self::decode($m[4]);

			// <link rel="image_src" href="http://infox.ru/photos/2011/17/112717/300x168_IRp6fXolYdFHbUso28YKRYQS8y3fn0Ca.jpg" >
			if(preg_match("!<link [^>]*rel=['\"]?([\w:]+)['\"]?[^>]+(href)=\"(.*?)\"!is", $s, $m))
				$meta[bors_lower($m[1])] = self::norm($url_data, self::decode($m[3]));
		}

		if(!empty($meta['og:title']))
			$meta['title'] = $meta['og:title'];

		if(empty($meta['title']) && !empty($meta['twitter:title']))
			$meta['title'] = $meta['twitter:title'];

		if(empty($meta['title']) && preg_match('!<title[^>]*>(.+?)</title>!si', $content, $m))
			$meta['title'] = self::decode($m[1]);

		if(empty($meta['title']) && preg_match('!<h1[^>]*>([^>]+)</h1>!si', $content, $m))
			$meta['title'] = self::decode($m[1]);

		if(!empty($meta['og:description']))
			$meta['description'] = $meta['og:description'];

		if(empty($meta['description']) && !empty($meta['twitter:description']))
			$meta['description'] = $meta['twitter:description'];

		$meta['host'] = @$url_data['host'];

		return $meta;
	}

	static function decode($text)
	{
		return trim(html_entity_decode(html_entity_decode($text, ENT_COMPAT, 'UTF-8'), ENT_COMPAT, 'UTF-8'));
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

	static function set_og_meta($view)
	{
		template_meta_prop('og:title',	$view->title());
		template_meta_prop('og:url',	$view->url_ex($view->page()));
		if($type = $view->get('meta_og_type'))
			template_meta_prop('og:type', $type);

		if($image = $view->get('image_url'))
			template_meta_prop('og:image', $image);
		elseif($image = $view->get('image'))
			template_meta_prop('og:image', $image->thumbnail("250x250")->url());

		if($description = $view->get('description'))
			template_meta_prop('og:description', $description);
		if($project = $view->get('project'))
			template_meta_prop('og:site_name', $project->title());
	}
}
