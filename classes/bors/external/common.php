<?php

class bors_external_common extends bors_object
{
	static function content_extract($url, $limit=1500)
	{
		if(preg_match('/\.(jpg|jpeg|png|gif)$/i', $url))
			return array('bbshort' => "[img url=\"$url\" 468x468]", 'tags' => array());

		$html = bors_lib_http::get_cached($url, 7200);
		$meta = bors_lib_html::get_meta_data($html);
//		echo "$url:<br/>"; print_dd($meta); exit();

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

		if($img)
			$img = "[img {$img} 200x200 left flow]";

		if($title && $description)
		{
			$description = clause_truncate_ceil($description, $limit);

			$bbshort = "[round_box]{$img}[h][url={$url}]{$title}[/url][/h]
{$description}

// ".bors_external_feeds_entry::url_host_link($url)."[/round_box]";

			$tags = array();
			return compact('tags', 'bbshort');
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
