<?php

// Парсинг RSS с простых ресурсов с HTML-контентом, *тегами в шапке и без заголовка

require_once('engines/lcml/main.php');

class bors_external_juick extends bors_object
{
	static function parse($data)
	{
		require_once('/var/www/bors/composer/vendor/autoload.php');

		$tags = popval($data, 'tags', array());
		$feed = popval($data, 'feed');
		if($raw_data = popval($data, 'raw_data'))
		{
			$f = new SimplePie();
			$f->set_feed_url($feed->feed_url());
			$f->enable_cache(false);
			$f->init();

			$item = new SimplePie_Item($f, unserialize($raw_data));
			$item->set_registry($f->get_registry());
		}
		else
			$item = NULL;

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

		$text = preg_replace_callback('!<a [^>]*href="(http://pics\.livejournal\.com/[^"]+)"[^>]*>pics\.livejournal\.com</a>!',
			function($m) { return lcml("[img]{$m[1]}[/img]");}, $text);

		$text = preg_replace_callback('!<a href="[^"]+youtube[^"]+v=([^"&]+)?"[^>]+>youtube\.com</a>!i',
			function($m) { return lcml("[youtube]{$m[1]}[/youtube]");}, $text);

		$text = preg_replace_callback('!<a href="([^"]+?\.(png|jpg|jpeg|gif))"[^>]+?>[\w\.]+</a>!i',
			function($m) { return lcml("[img]{$m[1]}[/img]");}, $text);

		$text = preg_replace_callback('!<a href="https?://picasaweb.google.com/lh/photo/([^"\?/]+)\?feat=directlink" rel="nofollow">picasaweb.google.com</a>!i',
			function($m) { return lcml("[picasa]{$m[1]}[/picasa]");}, $text);

		$text = preg_replace_callback('!^<a href="(http://([^/"]+)[^"]+)"[^>]*>\2</a><br />!mi',
			function($m) { return lcml($m[1]);}, $text);

		if($item)
		{
			$encs = $item->get_enclosures();
			if($encs)
			{
				$first = true;
				$shown = false;
				foreach($encs as $enc)
				{
					if($enc->get_link())
					{
						if($first)
							$text .= "<hr />";
						$first = false;
						$shown = true;
					}

					switch($enc->get_type())
					{
						case 'image/jpeg':
						case 'image/png':
						case 'image/gif':
							$text .= "<br/>\n".lcml_bbh("[img=\"{$enc->get_link()}\" 640x640]");
							break;
						default:
							if($enc->get_link())
								$text .= "<br/>\n".lcml_bbh("[url]{$enc->get_link()}[/url]");
							break;
					}
				}

				if($shown)
					$text .= "<hr />";
			}
		}

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
