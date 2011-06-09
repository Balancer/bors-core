<?php

class bors_external_other extends bors_object
{
	static function content_extract($url)
	{
		if(preg_match('/\.(jpg|jpeg|png|gif)$/i', $url))
			return array('bbshort' => "[img url=\"$url\" 468x468]", 'tags' => array());

		$html = bors_lib_http::get_cached($url, 7200);
		$meta = bors_lib_html::get_meta_data($html);
//		echo "$url:<br/>"; print_dd($meta); exit();

		if(!empty($meta['title']) && !empty($meta['description']))
		{
			if($img = @$meta['image_src'])
				$meta['description'] = "[img {$img} 200x200 left] ".$meta['description'];

			$meta['description'] = clause_truncate_ceil($meta['description'], 1024);

			$bbshort = "[b][url={$url}]{$meta['title']}[/url][/b]

{$meta['description']}

// ".ec("Подробнее: ").bors_external_feeds_entry::url_host_link($url);

			$tags = array();
			return compact('tags', 'bbshort');
		}

		return NULL;
	}
}
