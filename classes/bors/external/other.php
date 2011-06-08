<?php

class bors_external_other extends bors_object
{
	static function content_short_extract($url)
	{
		$html = bors_lib_http::get_cached($url, 7200);
		$meta = bors_lib_html::get_meta_data($html);
//		echo "$url:<br/>"; print_dd($meta); exit();

		if(!empty($meta['title']) && !empty($meta['description']))
		{
			if($img = @$meta['image_src'])
				$meta['description'] = "[img {$img} 200x200 left] ".$meta['description'];

			$meta['description'] = clause_truncate_ceil($meta['description'], 1024);

			return "[b][url={$url}]{$meta['title']}[/url][/b]

{$meta['description']}

// ".ec("Подробнее: ").bors_external_feeds_entry::url_host_link($url);
		}

		if(class_exists('DOMDocument'))
		{
//			$dom = new DOMDocument;
//			@$dom->loadHTML($html);
//			$x = $dom->getElementsByTagName('img')->item(0);
//			return "[img]{$x->getAttribute('src')}[/img]";
		}

		return NULL;
	}
}
