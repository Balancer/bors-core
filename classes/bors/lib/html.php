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

	 		$val  = $m->getAttribute('content');
			if(!$val)
		 		$val  = $m->getAttribute('value');

			if($property)
		    	$meta[$property] = $val;
	    }

//		if(config('is_developer')) { var_dump($content, $meta); exit('html-meta'); }

		$content = preg_replace("'<style[^>]*>.*</style>'siU",'',$content);  // strip js
		$content = preg_replace("'<script[^>]*>.*</script>'siU",'',$content); // strip css

		foreach(explode("\n", $content) as $s)
			if(preg_match("!<link rel=\"([^\"]+)\" href=\"([^\"]+)\" />!i", trim($s), $m))
				$meta[$m[1]] = $m[2];

		$content = str_replace("\n", " ", $content);
		$content = preg_replace("!<meta !i", "\n<meta ", $content);
		$content = preg_replace("!/>!", "/>\n", $content);

		$url_data = parse_url($url);

		foreach(explode("\n", $content) as $s)
		{
			if(preg_match("!<meta[^>]+(http\-equiv|name|property)=['\"]([\w:]+)['\"][^>]+(content|value)='([^']*)'!is", trim($s), $m))
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

	static function set_og_meta($view)
	{
		template_meta_prop('og:title',	$view->title());
		template_meta_prop('og:url',	$view->url_ex($view->page()));
		if($type = $view->get('meta_og_type'))
			template_meta_prop('og:type', $type);
		if($image = $view->get('image'))
			template_meta_prop('og:image', $image->thumbnail("200x200")->url());
		if($description = $view->get('description'))
			template_meta_prop('og:description', $description);
		if($project = $view->get('project'))
			template_meta_prop('og:site_name', $project->title());
	}
}
