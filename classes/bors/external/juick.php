<?php

// Парсинг RSS с простых ресурсов с HTML-контентом, *тэгами в шапке и без заголовка

class bors_external_juick extends bors_object
{
	static function parse($data)
	{
		$tags = popval($data, 'tags', array());

//		var_dump($data);
//		exit();

		extract($data);

		if(preg_match_all('/(^|\s)\*([\wа-яА-ЯёЁ]+)/um', $text, $matches))
		{
			foreach($matches[2] as $m)
			{
				$tags[] = $m;
				$text = preg_replace('/(^|\s)\*'.preg_quote($m).'/um', '\1', $text);
			}
		}

		$text = preg_replace('!<a href="[^"]+youtube[^"]+v=([^"&]+)?"[^>]+>youtube\.com</a>!ie', "lcml('[youtube]$1[/youtube]');", $text);
		$text = preg_replace('!<a href="([^"]+?\.(png|jpg|jpeg|gif))"[^>]+?>[\w\.]+</a>!ie', "lcml('[img]$1[/img]');", $text);
		$text = preg_replace('!<a href="https?://picasaweb.google.com/lh/photo/([^"\?/]+)\?feat=directlink" rel="nofollow">picasaweb.google.com</a>!ie', "lcml('[picasa]$1[/picasa]');", $text);
		$text = preg_replace('!^<a href="(http://([^/"]+)[^"]+)"[^>]*>\2</a><br />!mie', "lcml('$1');", $text);
//		var_dump($text); exit();

		if(!empty($link))
			$text .= "<br/><br/><span class=\"transgray\">// Транслировано с ".bors_external_feeds_entry::url_host_link_html($link)."</span>";

		return array(
			'title' => NULL,
			'text' => $text,
			'tags' => $tags,
			'bb_code' => $text,
			'markup' => 'bors_markup_html',
		);
	}
}
