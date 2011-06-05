<?php

class bors_external_other extends bors_object
{
	static function content_short_extract($url)
	{
		$html = bors_lib_http::get($url);
		$meta = bors_lib_html::get_meta_data($html);
//		print_dd($meta);

		if(!empty($meta['title']) && !empty($meta['description']))
		{
			return "[b]{$meta['title']}[/b]\n\n{$meta['description']}";
		}

		if(class_exists('DOMDocument'))
		{
//			$dom = new DOMDocument;
//			@$dom->loadHTML($html);
//			$x = $dom->getElementsByTagName('img')->item(0);
//			return "[img]{$x->getAttribute('src')}[/img]";
		}

		return $url;
	}
}
