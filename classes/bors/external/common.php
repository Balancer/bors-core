<?php

class bors_external_common extends bors_object
{
	static function content_extract($url, $limit=1500)
	{
		if(preg_match("/\.(pdf|zip|rar|djvu|mp3|avi|mkv|mov|mvi|qt|ppt)$/i", $url))
			return array('bbshort' => "[img url=\"$url\" 468x468]", 'tags' => array());

		$more = false;

//		$html = bors_lib_http::get($url);
		$html = bors_lib_http::get_cached($url, 7200 /*, false, true*/ ); // Для сборса кеша
		$meta = bors_lib_html::get_meta_data($html, $url);

//		if(config('is_developer')) { echo "$url:<br/>"; var_dump($html); exit(); }

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

		// Яндекс.Видео — такое Яндекс.Видео...
		// http://balancer.ru/g/p2728087 для http://video.yandex.ru/users/cnewstv/view/3/
		if(strpos($title, "\\'") !== false)
			$title = stripslashes($title);
		if(strpos($description, "\\'") !== false)
			$description = stripslashes($description);

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

		// Андроид Маркет
		// <div class="doc-banner-icon"><img src="https://g1.gstatic.com/android/market/com.eolwral.osmonitor/hi-256-1-cb0eccad4104c6cf15182a6da90c40002d76bad8" /></div>
		// <div class="doc-banner-icon"><img src="https://lh5.ggpht.com/HelkQpBcO9SPqOgu0AdXqU_N6M3zMIBR6lR-rvBUPMsZl_7H2aGwfqq9tEHV89vJ_yo=w124"/></div>
		if(!$img && preg_match('!<div class="doc-banner-icon">\s*<img src="([^"]+)"\s*/>\s*</div>!s', $html, $m))
			$img = $m[1];

		// Принудительный urldecode, если нужно.
		if(preg_match('/%D0/', $img))
			$img = urldecode($img);

		if(preg_match('/^\w+/', $img) && !preg_match('/^\w+:/', $img)) // Это тупо "images/stories/img/big/m1a2_2.jpg" — вроде, как от корня сайта
			$img = 'http://'.$meta['host'].'/'.$img;

		// http://www.balancer.ru/g/p3038945
		// http://www.rg.ru/2013/01/17/voda.html
		if(preg_match('!^/!', $img)) // от корня сайта
			$img = 'http://'.$meta['host'].$img;

		if(!$img)
		{
			// Ставим герерацию превьюшки
			if(preg_match('/%\w\w%/i', $url))
				$url = urldecode($url);

			$url_data = parse_url($url);
			$host = preg_replace('/^www\./', '', $url_data['host']);
			$host_parts = array_reverse(explode('.', $host));

			$id = blib_string::base64_encode2($url);

			$img = "http://www.balancer.ru/_cg/_st/{$host_parts[0]}/{$host_parts[1][0]}/{$host_parts[1]}/{$id}-400x300.png";
			// Дёрнем, чтобы сгенерировалось
			$x = blib_http::get_bin($img, array('timeout' => 15));

			if(config('is_debug') && !$x)
			{
				var_dump($url, $id, $host_parts, "http://www.balancer.ru/_cg/_st/{$host_parts[0]}/{$host_parts[1][0]}/{$host_parts[1]}/{$id}-400x300.png", $x);
				exit();
			}
		}

		if($img)
			$img = "[img={$img} 200x200 left flow nohref]";

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

//		if(config('is_developer')) { var_dump($description); exit(); }

		if(!$description)
		{
			$dom = new DOMDocument('1.0', 'UTF-8');
			$html = preg_replace('!<meta [^>]+?>!is', '', $html);
			$html = iconv('utf-8', 'utf-8//ignore', $html);
//			if(config('is_developer')) { var_dump($html); exit(); }
			$html = preg_replace('!<script[^>]*>.+?</script>!is', '', $html);
			$html = str_replace("\r", "", $html);

			if($html)
			{
				libxml_use_internal_errors(true);
				$dom->loadHTML($html);
				$xpath = new DOMXPath($dom);

				foreach(array(
					'//script',
					'//style',
				) as $query)
					foreach($xpath->query($query) as $node)
						$node->parentNode->removeChild($node);

//				if($divs = $xpath->query('//div[@id="content"]'))
				// Тест на http://www.balancer.ru/g/p2982207
				if($divs = $xpath->query('//p'))
				{
					$content = /*bors_lib_dom::element_html*/($divs->item(0));
					$source = preg_replace('/<!--.*?-->/s', '', @$content->nodeValue);
					if(strpos($source, 'Ð') !== false)
					{
						// Грязный хак для непонятных «Ð�Ð»Ð°Ð²Ð° Ð°Ð´Ð¼Ð¸Ð½Ð¸Ñ�Ñ�Ñ�Ð°Ñ�Ð¸Ð¸ Ð¿Ñ�ÐµÐ·Ð¸Ð»
						$source = iconv('utf8', 'latin1//ignore', $source);
						// Хак для убирания порезанных в конце символов
						$source = bors_substr($source, 0, bors_strlen($source) - 1);
//						if(config('is_developer')) { var_dump($source); exit(); }
					}
//					var_dump($source); exit();
					$source = preg_replace("/\s*\n+\s*/", "\n", $source);
					$source = array_filter(explode("\n", $source));
					if(count($source) > 7)
					{
						$source = array_slice($source, 0, 6);
						$source[] = ec('…');
						$more = true;
					}

					$source = join("\n", $source);

//					var_dump($source); exit();
					require_once('inc/texts.php');
					$description = clause_truncate_ceil($source, 512);
					if($source != $description)
						$more = true;
				}
			}
			else
				$description = '';
		}

		if($title && strlen($title) > 5)
		{
			require_once('inc/texts.php');
			$description = clause_truncate_ceil($description, $limit);

			// Из-за таких козлов:
			// http://www.balancer.ru/g/p2977129
			// http://www.balancer.ru/g/p2981105
			$title = htmlspecialchars(strip_tags($title));
			$description = htmlspecialchars(strip_tags($description));

			$bbshort = "[round_box]{$img}[h][a href=\"{$url}\"]{$title}[/a][/h]
{$description}

[span class=\"transgray\"][reference]".($more ? ec('Дальше — '):'').bors_external_feeds_entry::url_host_link($url)."[/reference][/span][/round_box]";

			$tags = array();

			$bbshort = trim(bors_close_tags(bors_close_bbtags($bbshort)));

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

		return call_user_func(array($parser, 'content_extract'), $url, $limit);
	}
}
