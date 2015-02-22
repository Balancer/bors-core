<?php

function lt_img($params)
{
	if(!trim($params['orig']))
		return '[img]';

	if(!empty($params['img']))
		$params['url'] = $params['img'];

	if(!empty($params['htmldecode']))
		$params['description'] = bors_entity_decode($params['description']);

	while(preg_match('/%\w+/', $params['url']))
		$params['url'] = urldecode($params['url']);

	if(preg_match('/&amp;/', $params['url']))
		$params['url'] = html_entity_decode($params['url']);

	// Если расширение не указано, дописываем .jpg
	if(!preg_match('/\.(jpe?g|png|gif)$/i', $params['url']))
		if(preg_match('!^(https?://lh\d+\.googleusercontent\.com/\S+)$!i', $params['url']))
				$params['url'] .= '.jpg';

	if(preg_match('!(\w+)://\d+!', $params['url'], $m) && $m[1] != 'http')
		return lt_img_bors($params);

	$url = bors()->main_object() ? bors()->main_object()->url() : NULL;
//	require_once('inc/airbase/images.php');
//	$data = airbase_image_data($params['url'], $url);

//	if(!$data['local'])
//		return "<a href=\"{$params['url']}\">{$params['url']}</a>"; // Временно отрубаем утягивание картинок.

	if(empty($params['size']))
	{
		$size = config('box_sizes', 640);
		if(is_numeric($size))
			$params['size'] = $size.'x'.$size;
		else
			$params['size'] = $size;
	}

	list($geo_w, $geo_h) = explode('x', $params['size']);

	if(!empty($params['url']))
	{
		$path = NULL;
		@$uri = html_entity_decode($params['url'], ENT_COMPAT, 'UTF-8');

		// Заменим ссылку в кеш на полную картинку
		require_once('inc/filesystem.php');

		if($main_object = bors()->main_object())
			$uri = secure_path(abs_path_from_relative(preg_replace("!^(.+?)/cache/(.+)/\d*x\d*/(.+?)$!", "$1/$2/$3", $uri), $main_object->url()));

		$data = url_parse($uri);

		if($data['local'])
		{
		   	$fp = preg_replace("!^(.*?)/([^/]+)$!", "$1/img/$2", $data['local_path']);
			if(file_exists($fp))
			{
				$path  = $fp; // локальный путь
				$uri   = preg_replace("!^(.*?)/([^/]+)$!", "$1/img/$2", $data['uri']);
			}
			else
			{
				$fp = $data['local_path'];
				if(file_exists($fp))
				{
					$path  = $fp; // локальный путь
					$uri   = $data['uri'];
				}
			}
		}

		$data = url_parse($uri);

		if(!file_exists($path) && $data['local'])
		{
			$path = $data['local_path'];
			$uri  = $data['uri'];
		}

		if(preg_match('/\w{5,}$/', $data['path']))
			$data['path'] .= '.jpg';

//		if(config('is_debug') && preg_match('/kaban.*png/', $uri)) { echo '<xmp>', $uri,' '; var_dump($params); var_dump($data); exit(); }

		$store_path = config('sites_store_path');
		$store_url  = config('sites_store_url');

		if(!empty($params['flow']))
			$align_class = ' pull-left';
		else
			$align_class = '';

		if(!empty($params['width']) && !empty($params['height']))
			$err_box = "<div class=\"alert alert-danger{$align_class}\" style=\"width: {$params['width']}px; width: {$params['height']}px; overflow: hidden; margin: 0 8px 0 0;\">%s</div>";
		else
			$err_box = "%s";

		if(preg_match('!/_cg/!', $uri))
		{
			$path = $data['path'];
			$file = $data['local_path'];;
			$store_url  = 'http://www.balancer.ru';
			$store_path = str_replace($path, '', $data['local_path']);
//			var_dump($path, $file, $data, $store_path); exit();
		}
		else
		{
			$path = $data['path'];
//			if(config('is_developer')) { echo '<xmp>'; var_dump($data, $f, $store_path); exit(); }

			//TODO: Придумать, что сделать с этим хардкодом.
			if(file_exists($data['local_path']) || preg_match('!/var/www!', $data['local_path']))
				$file = $data['local_path'];
			else
				$file = "$store_path$path";
		}

		if(!$data['local'] || !file_exists($file))
		{
			$path = "{$data['host']}{$data['path']}";

			if(preg_match("!/$!",$path))
				$path .= "index";

			if(!empty($data['query']))
				$path .= '/='.str_replace('&','/', $data['query']);

			$file = "$store_path/$path";
			if(!file_exists($file) || filesize($file)==0)
			{
				$c1 = bors_substr($data['host'],0,1);
				$c2 = bors_substr($data['host'],1,1);
				require_once('inc/urls.php');
				$path = "$c1/$c2/{$data['host']}".translite_path($data['path']);

				if(preg_match("!/$!",$path))
					$path .= "index";

				if(!empty($data['query']))
					$path .= '/='.str_replace('&','/', $data['query']);

				$file = "$store_path/$path";
			}

			bors_debug::syslog('000-image-debug', "Get image size for ".$file);
			if(!file_exists($file) || filesize($file)==0 || !($image_size = @getimagesize($file)))
			{
				$path = web_import_image::storage_place_rel($params['url']);
				// Тестировать http://www.balancer.ru/g/p3576581
				// Проверить http://www.balancer.ru/g/p3576651
				// Novice1975> [URL=http://radikal.ru/fp/a488e474eeee440090b1840e81f69ccc][img Мляяяяя....

				if(!$path)
					return "<a href=\"{$uri}\">{$uri}</a> <small style=\"color: #ccc\">[incorrect image]</small>";
				$file = "$store_path/$path";
			}

			bors_debug::syslog('000-image-debug', "Get image size for ".$file);
			$image_size = @getimagesize($file);

//			if(config('is_developer')) { echo '<xmp>'; var_dump($params['url'], $file, $image_size); }

			if($path && file_exists($file) && !$image_size)
			{
				//TODO: Придумать, что сделать с этим хардкодом.
				$thumbnails = bors_find_all('bors_image_thumb', array(
					"full_file_name LIKE '%/".addslashes(basename($file))."'",
				));

				if($thumbnails)
					foreach($thumbnails as $t)
						$t->delete();

				unlink($file);
			}

			if(!file_exists($file) || filesize($file)==0 || !$image_size)
			{
				$path = web_import_image::storage_place_rel($params['url']);
				if(!$path)
					return "<a href=\"{$uri}\">{$uri}</a> <small style=\"color: #ccc\">[incorrect image]</small>";
				$file = "$store_path/$path";

				require_once('inc/filesystem.php');
				mkpath(dirname($file), 0777);

				if(!is_writable(dirname($file)))
				{
					bors_debug::syslog('file_access_error', "Can't write to ".dirname($file)."\nparams=".print_r($params, true));
					return sprintf($err_box, "<a href=\"{$params['url']}\">{$params['url']}</a><small class=\"gray\"> [can't write '$file']</small>");
				}

				$x = blib_http::get_ex(str_replace(' ', '%20', $params['url']), array(
					'file' => $file,
					'is_raw' => true,
				));

				@chmod($file, 0666);

//				if(config('is_developer')) {var_dump($params['url'], $x); exit(); }

				$content_type = $x['content_type'];

				if(@filesize($file) <= 0)
					return "<a href=\"{$uri}\">{$uri}</a> <small style=\"color: #ccc\">[zero size or time out]</small>";

//				if(config('is_developer')) { var_dump($params, $content_type); exit(); }
				// Яндекс.Видео — такое Яндекс.Видео...
				// http://balancer.ru/g/p2728087 для http://video.yandex.ru/users/cnewstv/view/3/
				if($content_type
						&& !preg_match("!image!", $content_type)
						// http://www.balancer.ru/g/p3158050 — овнояндекс отдаёт картинку как text/html
						&& !preg_match('!img-fotki\.yandex\.ru/get/\d+!', $params['url'])
					)
				{
//					debug_hidden_log('images-error', $params['url'].ec(': is not image. ').$content_type."\n".$content); // Это не картинка
					return sprintf($err_box, lcml_urls_title($params['url']).'<small> [not image]</small>');
				}

				//TODO: придумать, блин, какой-нибудь .d вместо каталогов. А то, вдруг, картинка будет и прямая
				//и с GET-параметрами.

				// Автоматический фикс старого некорректного утягивания.
				// errstr=fopen(/var/www/balancer.ru/htdocs/sites/g/a/gallery.greedykidz.net/get/992865/3274i.jpg/=g2_serialNumber=1)
				if(preg_match('#^(.+\.(jpe?g|png|gif))/=#', $file, $m) && file_exists($m[1]))
					unlink($m[1]);
			}

			if(!$image_size)
			{
				bors_debug::syslog('000-image-debug', "Get image size for ".$file);
				$image_size = @getimagesize($file);
			}

//			if(config('is_developer')) { echo '<xmp>'; var_dump($params['url'], $file, $image_size); }

			if(file_exists($file) && filesize($file)>0 && $image_size)
			{
				$data['local_path'] = $_SERVER['DOCUMENT_ROOT'] . "/$path";
				$data['local'] = true;
			}

			// test: http://www.aviaport.ru/conferences/40911/rss/
			if(file_exists($file) && filesize($file)>0 && config('lcml.airbase.register.images'))
			{
				$remote = $uri;
				$uri = "$store_url/$path";
				$data['local'] = true;

				$db = new driver_mysql(config('main_bors_db'));

				$id = intval($db->select('images', 'id', array('original_url=' => $remote)));
				if(!$id)
				{
					$db->store('images', 'original_url=\''.addslashes($remote).'\'', array('original_url' => $remote));
					$id = $db->last_id();
				}

				$db->update('images', array('id' => $id), array('local_path' => $data['local_path']));
			}
		}

		if(config('lcml.airbase.register.images'))
		{
			$img = airbase_image::register_file($file, true, true, 'airbase_image');
			balancer_board_posts_object::register($img, $params);
		}

		if($data['local'])
		{
			if(!file_exists($file))
			{
//				if(config('is_developer')) { var_dump($file, $data); exit(); }
				debug_hidden_log('error_lcml_tag_img', "Incorrect image {$params['url']}");
				return lcml_urls_title($params['url']).'<small> [image link error]</small>';
			}

			if(preg_match('/\.gif$/i', $params['url']))
			{
				if(is_anigif($file))
				{
					$params['noresize'] = true;
					$params['nohref'] = true;
				}
			}

			if(preg_match('/airbase\.ru|balancer\.ru|wrk\.ru/', $data['uri'])
					&& preg_match('!^(http://[^/]+/cache/.+/)\d*x\d*(/[^/]+)$!', $data['uri'], $m))
				$img_ico_uri  = $m[1].$params['size'].$m[2];
			elseif(!empty($params['noresize']))
				$img_ico_uri  = $uri;
			elseif(preg_match('/airbase\.ru|balancer\.ru|wrk\.ru/', $data['uri']))
				$img_ico_uri  = preg_replace("!^(http://[^/]+)(.*?)(/[^/]+)$!", "$1/cache$2/{$params['size']}$3", $data['uri']);
			else
				$img_ico_uri  = preg_replace("!^(http://[^/]+)(.*?)(/[^/]+)$!", "$1/cache$2/{$params['size']}$3", "$store_url/$path");

//			if(config('is_developer')) { echo '<xmp>'; var_dump($file, $uri, $img_ico_uri, $data, $params); exit(); }

			if(preg_match('!\.[^/+]$!', $uri))
				$img_page_uri = preg_replace("!^(http://.+?)(\.[^\.]+)$!", "$1.htm", $uri);
			else
				$img_page_uri = $uri.'.htm';

			if(defval($params, 'is_direct') || defval($params, 'ajax'))
				$img_page_uri = $uri;

			if($href = defval($params, 'href'))
				$have_href = true;
			elseif(defval($params, 'use_cache'))
			{
				$href = $uri;
				$have_href = true;
			}
			else
			{
				$href = $img_page_uri;
				$have_href = false;
			}

//			if(config('is_developer')) { var_dump(defval($params, 'href'), defval($params, 'use_cache'), $uri, $href, $have_href); exit(); }
			if(!$have_href)
				$href = $uri;

			if($thumb = bors_load('bors_image_autothumb', preg_replace('!^http://[^/]+/cache/!', '/', $img_ico_uri)))
			{
				$thumb = $thumb->make_self();
				$thumb_file = $thumb->full_file_name();
			}
			else
				$thumb_file = NULL;

			if(file_exists($thumb_file))
			{
				$width	= $thumb->width();
				$height	= $thumb->height();
			}
			else
			{
				bors_debug::syslog('obsolete-image-thumbnail-by-url-making', "Making of thumbnail by url for ".$img_ico_uri);
				// Для совместимости старый код. Вдруг где-то не сработает?
				// Дёргаем превьюшку, чтобы могла сгенерироваться.
				// Кстати, ошибка может быть и от перегрузки.
				blib_http::get($img_ico_uri, true, 200000); // До 200кб

				bors_debug::syslog('000-image-debug', "Get image size for ".$img_ico_uri);
				list($width, $height, $type, $attr) = getimagesize($img_ico_uri);

				if(!intval($width) || !intval($height))
				{
					// Если с одного раза не сработало, пробуем ещё раз
					sleep(5);
					blib_http::get($img_ico_uri, true, 1000000); // До 1Мб

					bors_debug::syslog('000-image-debug', "Get image size for ".$img_ico_uri);
					list($width, $height, $type, $attr) = getimagesize($img_ico_uri);
				}
			}

			if(!intval($width) || !intval($height))
				return sprintf($err_box, "<a href=\"{$params['url']}\">{$params['url']}</a> [can't get <a href=\"{$img_ico_uri}\">icon's</a> size]");

			//TODO: придумать, как обойти хардкод имени класса картинки
			if($image = bors_image::register_file($file))
			{
				$img_w = $image->width();
				$img_h = $image->height();
			}
			else
			{
				bors_debug::syslog('000-image-debug', "Can't register image\n$file\n$uri");
				@list($img_w, $img_h) = @getimagesize($uri);
			}

//			if(config('is_debug')) { echo '<xmp>'; var_dump($uri, $file, $img_w, $img_h); exit('x'.__LINE__); }

			if(empty($params['description']))
				$params['description'] = "";
			if(empty($params['no_lcml_description']))
				$description = stripslashes(!empty($params['description']) ? lcml($params['description']) : '');
			else
				$description = stripslashes(!empty($params['description']) ? $params['description'] : '');

			$a_href_b = "";
			$a_href_e = "";

			$image_class = array('main');
			$ajax = defval($params, 'ajax');
			$styles = array();

			if($description)
				$title = " title=\"".htmlspecialchars(str_replace('www.', '&#119;ww.', strip_tags($description)))."\"";
			else
				$title = "";

			if(empty($params['nohref']) || $ajax)
			{
//				if(config('is_developer')) { var_dump($img_w, $width, $ajax, $href, $uri, $description); exit("one"); }
				if($img_w < $width*1.1 || $ajax == 'hoverZoom')
				{
					if($ajax == 'hoverZoom')
					{
//						$image_class[] = 'hoverZoom';
						$styles[] = 'hoverZoom';
					}

					$a_href_b = "<a href=\"{$href}\" class=\"thumbnailed-image-link\"{$title}>";
					$a_href_e = "</a>";
				}
				elseif(!preg_match('/\.htm$/', $href))
				{
					if($width > 300 && $height > 200)
						$rel = "position:'inside'";
					else
						$rel = "position:'bototm', zoomWidth:400, zoomHeight:400";

//					$lightbox_code = save_format(jquery_lightbox::html("'a.cloud-zoom'"));
					$lightbox_code = "";
					$a_href_b = "$lightbox_code<a href=\"{$href}\" class=\"cloud-zoom thumbnailed-image-link\" id=\"zoom-".rand()."\" rel=\"{$rel}\"{$title}>";
					$a_href_e = "</a>";
				}
				else
				{
					$a_href_b = "<a href=\"{$href}\"{$title}>";
					$a_href_e = "</a>";
				}

			}

//			if(config('is_developer')) ~r($href, $title, $description, $a_href_b);

			$out = '';

			if(@$params['border'])
			{
				if($width > 640) // Это чтобы не наезжало на аватар
					$out .= "<div class=\"clear\">&nbsp;</div>\n";

				$params['skip_around_cr'] = true;
				$styles[] = $description ? 'rs_box' : 'rs_box_nd';
			}

			if(@$params['flow'] == 'flow' && @$params['align'] != 'center')
			{
				if(@$params['align'] == 'left')
					$styles[] = 'float_left';
				if(@$params['align'] == 'right')
					$styles[] = 'float_right';
			}
			else
			{
				$styles[] = @$params['align'];
			}

			$styles[] = 'mtop8';
			$description = str_replace('%IMAGE_PAGE_URL%', $img_page_uri, $description);

			$out .= '<div class="'.join(' ', $styles)."\" style=\"width:".($width)."px;".(!$description? "height:".($height)."px" : "")
				.";\">{$a_href_b}<img src=\"$img_ico_uri\" width=\"$width\" height=\"$height\" alt=\"\" class=\"".join(' ', $image_class)."\" />{$a_href_e}";
			if($description)
				$out .= "<small class=\"inbox\">".$description."</small>";
			$out .= '</div>';

			return $out;
		}

		return "<a href=\"{$params['url']}\">{$params['url']}</a><small class=\"gray\"> [can't download]</small>";
	}

	return "<a href=\"{$params['url']}\">{$params['url']}</a><small class=\"gray\"> [empty url]</small>";
}

function lt_img_bors($params)
{
	$uri = $params['url'];
	$image = object_load($uri);
	if(!$image)
		return "Unknown image {$uri}";

	if(!($size = defval($params, 'size')))
		$size = '200x';

	$classes = array();
	$around_div_classes = array();
	switch(@$params['align'])
	{
		case 'left':
			$classes[] = 'float-left';
			break;
		case 'right':
			$classes[] = 'float-right';
			break;
		case 'center':
			$around_div_classes[] = 'center';
			break;
	}

	if(@$params['border_class'])
		$classes[] = $params['border_class'];

	$append = array();
	$around_beg = array();
	$around_end = array();

	if($classes)
		$append[] = "class=\"".join(' ', $classes)."\"";

	if($around_div_classes)
	{
		$around_beg[] = "<div class=\"".join(' ', $around_div_classes)."\">";
		$around_end[] = "</div>";
	}

	if($popup = @$params['popup'])
	{
		$around_beg[] = "<a href=\"/images/{$image->id()}/popup-{$popup}/\""
			." onClick=\"popupWin = window.open(this.href, 'image', 'width=1020,height=620,top=0'); popupWin.focus(); return false;\""
			." target=\"_blank\">";
		$around_end[] = "</a>";
	}

	global $lt_img_bors_parsed;
	$lt_img_bors_parsed[$uri] = true;

	if(@$params['noresize'])
		return join('', $around_beg).$image->html_code(join(' ', $append)).join('', array_reverse($around_end));
	else
		return join('', $around_beg).$image->thumbnail($size)->html_code(join(' ', $append)).join('', array_reverse($around_end));
}

// http://stackoverflow.com/questions/280658/can-i-detect-animated-gifs-using-php-and-gd
function is_anigif($filename)
{
    if(!($fh = @fopen($filename, 'rb')))
        return false;

    $count = 0;
    //an animated gif contains multiple "frames", with each frame having a
    //header made up of:
    // * a static 4-byte sequence (\x00\x21\xF9\x04)
    // * 4 variable bytes
    // * a static 2-byte sequence (\x00\x2C)

    // We read through the file til we reach the end of the file, or we've found
    // at least 2 frame headers
    while(!feof($fh) && $count < 2)
	{
        $chunk = fread($fh, 1024 * 100); //read 100kb at a time
        $count += preg_match_all('#\x00\x21\xF9\x04.{4}\x00\x2C#s', $chunk, $matches);
    }

    fclose($fh);
    return $count > 1;
}
