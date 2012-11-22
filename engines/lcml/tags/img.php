<?php

require_once('inc/urls.php');

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

	if(preg_match('!(\w+)://\d+!', $params['url'], $m) && $m[1] != 'http')
		return lt_img_bors($params);

	$url = bors()->main_object() ? bors()->main_object()->url() : NULL;
//	require_once('inc/airbase/images.php');
//	$data = airbase_image_data($params['url'], $url);

	if(preg_match('/\.gif$/i', $params['url']))
	{
		$params['noresize'] = true;
		$params['nohref'] = true;
	}

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

			if(!$data['local'])
			{
				$path = config('sites_store_path')."/{$data['host']}{$data['path']}";

				if(preg_match("!/$!",$path))
					$path .= "index";

				if(!empty($data['query']))
					$path .= '/='.str_replace('&','/', $data['query']);

				if(!file_exists($path) || filesize($path)==0)
				{
					$c1 = bors_substr($data['host'],0,1);
					$c2 = bors_substr($data['host'],1,1);
					require_once('inc/urls.php');
					$path = config('sites_store_path')."/$c1/$c2/{$data['host']}".translite_path($data['path']);

					if(preg_match("!/$!",$path))
						$path .= "index";

					if(!empty($data['query']))
						$path .= '/='.str_replace('&','/', $data['query']);
				}

				$image_size = @getimagesize($path);
				if(!file_exists($path) || filesize($path)==0 || !$image_size)
				{
					require_once('inc/http.php');
					$x = http_get_ex(str_replace(' ', '+', $params['url']));
					$content      = $x['content'];
					$content_type = $x['content_type'];

					if(strlen($content) <= 0)
						return "<a href=\"{$uri}\">{$uri}</a> <small style=\"color: #ccc\">[zero size or time out]</small>";

					// Яндекс.Видео — такое Яндекс.Видео...
					// http://balancer.ru/g/p2728087 для http://video.yandex.ru/users/cnewstv/view/3/
					if($content_type && !preg_match("!image!", $content_type))
					{
//						debug_hidden_log('images-error', $params['url'].ec(': is not image. ').$content_type."\n".$content); // Это не картинка
						return lcml_urls_title($params['url']).'<small> [not image]</small>';
					}

					//TODO: придумать, блин, какой-нибудь .d вместо каталогов. А то, вдруг, картинка будет и прямая
					//и с GET-параметрами.

					// Автоматический фикс старого некорректного утягивания.
					// errstr=fopen(/var/www/balancer.ru/htdocs/sites/g/a/gallery.greedykidz.net/get/992865/3274i.jpg/=g2_serialNumber=1)
					if(preg_match('#^(.+\.(jpe?g|png|gif))/=#', $path, $m) && file_exists($m[1]))
						unlink($m[1]);

					require_once('inc/filesystem.php');
					mkpath(dirname($path), 0777);
					if(!is_writable(dirname($path)))
					{
						debug_hidden_log('access_error', "Can't write to ".dirname($path));
						return "<a href=\"{$params['url']}\">{$params['url']}</a><small class=\"gray\"> [can't write '$path']</small>";
					}


					$fh = fopen($path,'wb');
					fwrite($fh, $content);
					fclose($fh);
					@chmod($path, 0666);
				}

				// test: http://www.aviaport.ru/conferences/40911/rss/
				if(file_exists($path) && filesize($path)>0 && config('lcml.airbase.register.images'))
				{
					$remote = $uri;
					$uri = str_replace(config('sites_store_path'), config('sites_store_url'), $path);
					$data['local'] = true;

					$db = new driver_mysql(config('main_bors_db'));

					$id = intval($db->select('images', 'id', array('original_url=' => $remote)));
					if(!$id)
					{
						$db->store('images', 'original_url=\''.addslashes($remote).'\'', array('original_url' => $remote));
						$id = $db->last_id();
					}

					$db->update('images', array('id' => $id), array('local_path' => $path));

					$img = airbase_image::register_file($path, true, true, 'airbase_image');
					balancer_board_posts_object::register($img, $params);
				}
			}

			if($data['local'])
			{
				if(!file_exists($path))
				{
					debug_hidden_log('error_lcml_tag_img', "Incorrect image {$params['url']}");
					return lcml_urls_title($params['url']).'<small> [image link error]</small>';
				}

				if(!empty($params['noresize']))
					$img_ico_uri  = $uri;
				else
					$img_ico_uri  = preg_replace("!^(http://[^/]+)(.*?)(/[^/]+)$!", "$1/cache$2/{$params['size']}$3", $uri);

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

//				if(config('is_developer')) { var_dump(defval($params, 'href'), defval($params, 'use_cache'), $uri, $href, $have_href); exit(); }
				if(!$have_href)
					$href = $uri;

				// Дёргаем превьюшку, чтобы могла сгенерироваться.
				blib_http::get($img_ico_uri, true);

				list($width, $height, $type, $attr) = @getimagesize($img_ico_uri);
				@list($img_w, $img_h) = getimagesize($uri);

				if(!intval($width) || !intval($height))
					return "<a href=\"{$params['url']}\">{$params['url']}</a> [can't get <a href=\"{$img_ico_uri}\">icon's</a> size]";

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

				if(empty($params['nohref']) || $ajax)
				{
//					if(config('is_developer')) { var_dump($img_w, $width, $ajax, $href, $uri, $description); exit("one"); }
					if($img_w < $width*1.1 || $ajax == 'hoverZoom')
					{
						if($ajax == 'hoverZoom')
						{
//							$image_class[] = 'hoverZoom';
							$styles[] = 'hoverZoom';
						}

						$a_href_b = "<a href=\"{$href}\">";
						$a_href_e = "</a>";
					}
					elseif(!preg_match('/\.htm$/', $href))
					{
						if($width > 300 && $height > 200)
							$rel = "position:'inside'";
						else
							$rel = "position:'bototm', zoomWidth:400, zoomHeight:400";

//						$lightbox_code = save_format(jquery_lightbox::html("'a.cloud-zoom'"));
						$lightbox_code = "";
						$a_href_b = "$lightbox_code<a href=\"{$href}\" class=\"cloud-zoom\" id=\"zoom-".rand()."\" rel=\"{$rel}\">";
						$a_href_e = "</a>";
					}
					else
					{
						$a_href_b = "<a href=\"{$href}\">";
						$a_href_e = "</a>";
					}

				}

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
