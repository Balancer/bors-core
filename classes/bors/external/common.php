<?php

class bors_external_common extends bors_object
{
	static function content_extract($url, $limit=1500)
	{
		if(preg_match('/\.(jpg|jpeg|png|gif)$/i', $url))
			return array('bbshort' => "[img url=\"$url\" 468x468]", 'tags' => array());

		$html = bors_lib_http::get_cached($url, 7200);
		$meta = bors_lib_html::get_meta_data($html, $url);

//		if(config('is_developer')) { echo "$url:<br/>"; var_dump($meta); var_dump($html); exit(); }

		$title = @$meta['og:title'];
		if(!$title)
			$title = @$meta['title'];

		$description = @$meta['og:description'];
		if(!$description)
			$description = @$meta['description'];

		$img = @$meta['og:image'];
		if(!$img)
			$img = @$meta['img_src'];

		if(!$img)
			$img = @$meta['image_src'];

		if(!$img && preg_match('!<div class="thumbinner".+?<img .+src="(//upload.wikimedia.org/[^"]+\.jpg)"!', $html, $m))
			$img = 'http:'.$m[1];

		// Yandex.Market: <a id="id1164306191641" href="http://mdata.yandex.net/i?path=b0410004559__Philips-FC-9071-xl.jpg" target="_blank">
		if(!$img && preg_match('!<a id="\w+?" href="([^"]+\.jpg)" target="_blank">!', $html, $m))
			$img = $m[1];

		// Lenta.Ru: http://balancer.ru/g/p2579554
		if(!$img && preg_match('!<div class=photo><img src=(http://img.lenta.ru\S+) !', $html, $m))
			$img = $m[1];


		// Lenta.Ru: http://balancer.ru/g/p2580440
		if(!$img && preg_match('!^<img src=(http://img.lenta.ru/news/\S+\.jpg) width=!m', $html, $m))
			$img = $m[1];

		if($img)
			$img = "[img {$img} 200x200 left flow]";

/*
		if(!$img && config('is_developer'))
		{
			print_dd($html);
			$dom = new DOMDocument('1.0', 'UTF-8');
			$dom->loadHTML($html);
			$xpath = new DOMXPath($dom);
			$images = $xpath->query('//img');
			foreach($images as $x)
				var_dump($x->getAttribute('src'));
		}
if(config('is_developer')) { exit($img); }
*/

		if($title && strlen($title) > 5)
		{
			$description = clause_truncate_ceil($description, $limit);

			$bbshort = "[round_box]{$img}[h][url={$url}]{$title}[/url][/h]
{$description}

// ".bors_external_feeds_entry::url_host_link($url)."[/round_box]";

			$tags = array();
			return compact('tags', 'title', 'bbshort');
		}

		if(preg_match('!^(http://)pda\.(.+)$!', $url, $m))
			return self::content_extract($m[1].$m[2]);

		return NULL;
	}

	static function find_and_extract($url, $limit = 1500)
	{
		$udata = parse_url($url);
		if(preg_match('/livejournal\.com$/', $udata['host']))
			$parser = 'bors_external_livejournal';
		elseif($udata['host'] == 'bash.org.ru')
			$parser = 'bors_external_bashorgru';
		elseif($udata['host'] == 'www.aviaport.ru')
			$parser = 'bors_external_aviaport';
		elseif($udata['host'] == 'pda.lenta.ru')
			$parser = 'bors_external_pdalentaru';
		elseif(preg_match('/(last\.fm|lastfm\.ru)$/', $udata['host']))
			$parser = 'bors_external_lastfm';
		else
			$parser = 'bors_external_common';

		return $parser::content_extract($url, $limit);
	}
}
