<?php

class bors_external_common extends bors_object
{
	static function content_extract($url, $params = array())
	{
		if(!is_array($params))
			$limit = $params; // Раньше второй параметр был длиной
		else
			$limit = defval($params, 'limit', 5000); // Теперь — из массива аргументов

		$original_url = defval($params, 'original_url', $url);

		if(preg_match(config('urls.skip_load_ext_regexp'), $url))
			return array('bbshort' => "[img url=\"$url\" 468x468]", 'tags' => array());

		$more = false;

		$html = defval($params, 'html');
		if(!$html)
		{
			if(config('lcml_cache_disable_full'))
				$html = blib_http::get_cached($url, 7200, false, true); // Для сборса кеша
			else
				$html = blib_http::get_cached($url, 7200);

			$html = @iconv('utf-8', 'utf-8//ignore', $html);
		}

//		if(config('is_developer')) var_dump($html);
//		$html = bors_lib_http::get($url);

		$meta = bors_lib_html::get_meta_data($html, $url);

//		if(config('is_developer')) { var_dump($original_url, $url, $meta, $html); print_dd($html); echo debug_trace(); exit('meta'); }

		if(preg_match('/503 - Forwarding failure/', $html))
			$html = '';

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

		// Уже не работает. Пример:
		// http://en.wikipedia.org/wiki/Merlin_(rocket_engine_family)
		// http://www.balancer.ru/g/p3367358
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

		// ВКонтакте, картинка, http://www.balancer.ru/g/p3416257
		if(!$img && preg_match('!page_post_thumb_last_row"><img src="([^"]+)"!', $html, $m))
			$img = $m[1];

		// Андроид Маркет
		// <div class="doc-banner-icon"><img src="https://g1.gstatic.com/android/market/com.eolwral.osmonitor/hi-256-1-cb0eccad4104c6cf15182a6da90c40002d76bad8" /></div>
		// <div class="doc-banner-icon"><img src="https://lh5.ggpht.com/HelkQpBcO9SPqOgu0AdXqU_N6M3zMIBR6lR-rvBUPMsZl_7H2aGwfqq9tEHV89vJ_yo=w124"/></div>
		// http://www.balancer.ru/g/p2369967
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

		if($x = blib_http::get_bin($img, array('timeout' => 1)))
		{
			if(!preg_match('!^image/(png|jpeg|gif)!', $x['content_type']))
			{
				bors_debug::syslog('dev-snip-no-image', "$img: ".print_r($x, true));
//				if(config('is_developer')) { var_dump($x); exit(); }
				$img = NULL;
			}
		}
		else
			$img = NULL;

		// Если превью не нашли, смотрим, нет ли в параметрах превью по умолчанию
		if(!$img && ($regexp = defval($params, 'default_image_regexp')))
		{
			if(preg_match($regexp, $html, $m))
				$img = $m[defval($params, 'default_image_regexp_id', 1)];
		}

		if(!$img)
			$img = defval($params, 'default_image');

		if(!$img || !preg_match('/\.(jpe?g|png|gif)$/', $img))
		{
			// Ставим герерацию превьюшки
			// Сперва дектодируем URL (urldecode + кодировка)
			$url = blib_urls::decode($url);

			$url_data = parse_url($url);
			$host = preg_replace('/^www\./', '', $url_data['host']);
			$host_parts = array_reverse(explode('.', $host));

			$id = blib_string::base64_encode2($url);

			$img = "http://www.balancer.ru/_cg/_st/{$host_parts[0]}/{$host_parts[1][0]}/{$host_parts[1]}/{$id}-400x300.png";
			// Дёрнем, чтобы сгенерировалось
			$x = blib_http::get_bin($img, array('timeout' => 10));

			if(config('is_developer') && !$x)
			{
				var_dump($url, $id, $host_parts, "http://www.balancer.ru/_cg/_st/{$host_parts[0]}/{$host_parts[1][0]}/{$host_parts[1]}/{$id}-400x300.png", $x);
				exit();
			}
		}

		if($img)
			$img = "[img={$img} 200x200 left flow nohref resize]";

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

//		if(config('is_developer') && preg_match('/./', $url)) { var_dump($description); print_dd($html); exit(); }

		if(!$description)
		{
			$dom = new DOMDocument('1.0', 'UTF-8');
			$dom->encoding = 'UTF-8';
			$html = preg_replace('!<meta [^>]+?>!is', '', $html);
			$html = str_replace("\r", "", $html);

			// Режем нафиг весь JS-вывод, а то там бывает мусор тот ещё: http://www.balancer.ru/g/p3269156
			$html = preg_replace("!\.write(ln)?\(.+?\)!is", "", $html);
//			$html = preg_replace("!<script[^>]*>.*?</script>!si", " ", $html);

//			if(config('is_developer') && preg_match('/spb.ru/', $url)) { print_dd($html); exit('!description'); }

			if($html)
			{
				libxml_use_internal_errors(true);

//				<html><meta http-equiv="Content-Type" content="text/html; charset=utf-8"><body>' . $html . '</body></html>
				// http://www.balancer.ru/g/p3133962
				$dom->loadHTML('<?xml encoding="UTF-8">'.$html);
//				$dom->loadHTML($html);
				$xpath = new DOMXPath($dom);

//				if(config('is_developer')) { var_dump($query); print_dd($html); print_dd($dom->saveHTML()); }
				foreach(array(
					'//script',
					'//noscript',
					'//style',
					'//h1',
					'//h2',
					'//h3',
					'//h4',
					'//h5',
					'//h6',
					'//*[contains(@class, "nav")]',
					'//*[contains(@class, "keyword")]',
					'//*[contains(@class, "tag")]',
					'//*[contains(@class, "adv")]',
					'//*[contains(@class, "head")]',

					// Хардкод для форумной разметки
					'//*[@class="rep"]',
					'//*[@class="top-ad"]',
					'//*[@class="postsignature"]',
					'//*[contains(@class, "pages_select")]',
					'//*[contains(@class, "warning")]',
					'//*[contains(@class, "avatar_")]',

					// Хардкод для ВКонтакте: http://www.balancer.ru/g/p3416257
					'//div[@id="reg_bar_content"]',
					'//div[@id="quick_login"]',
					'//div[contains(@class, "full_wall_tabs")]',
				) as $query)
				{
					foreach($xpath->query($query) as $node)
						$node->parentNode->removeChild($node);

				}

//				if($divs = $xpath->query('//div[@id="content"]'))
				// Тест на http://www.balancer.ru/g/p2982207

//				if(config('is_developer')) { print_dd($dom->saveHTML()); }

				$divs = $xpath->query('//p');
				if(!$divs->length)
					$divs = $xpath->query('//div');

//				if(config('is_developer')) { var_dump($divs, $divs->length); }

				if($divs)
				{
					$source = array();
					for($i=0; $i<$divs->length; $i++)
					{
						$content = $divs->item($i);
//						if(config('is_developer')) { print_dd($dom->saveHTML($content)); var_dump($content->nodeValue); }
						$text = preg_replace('/<!--.*?-->/s', '', @$content->nodeValue);
						// Для http://www.balancer.ru/g/p1241837
						// В тексте может попасться ссылка, которая вызовет зацикливание lcml
						$text = preg_replace("!^\s*https?://\S+\s*$!im", '', $text);
						$text = preg_replace("!\s*https?://\S+\s*!is", '', $text);
						$text = preg_replace("/\n+/", ' ', $text);
						$source[] = trim($text);
					}

//					if(config('is_developer')) { var_dump($source); exit('src[]'); }

					$source = join("\n", $source);

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

			// Чистим в $description bb-code:
			$description = preg_replace('!\[/?\w+.*?\]!', '', $description);

			// Из-за таких козлов:
			// http://www.balancer.ru/g/p2977129
			// http://www.balancer.ru/g/p2981105
			$title = htmlspecialchars(strip_tags($title));
			$description = htmlspecialchars(strip_tags($description));

			$bbshort = "[round_box]{$img}[h][a href=\"{$original_url}\"]{$title}[/a][/h]
{$description}

[span class=\"transgray\"][reference]".($more ? ec('Дальше — '):'').bors_external_feeds_entry::url_host_link($original_url)."[/reference][/span][/round_box]";

//			if(config('is_developer')) { print_dd($bbshort); var_dump(restore_format(lcml($bbshort))); exit('bbcode'); }

			$tags = array();

			$bbshort = trim(bors_close_tags(bors_close_bbtags($bbshort)));

//			if(config('is_developer')) { print_dd($bbshort); exit($title); }
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
