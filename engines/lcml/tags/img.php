<?php

require_once('inc/urls.php');

function lt_img($params) 
{
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
		$params['size'] = '468x468';

		if(!empty($params['url']))
		{
			$path = NULL;
			@$uri = html_entity_decode($params['url'], ENT_COMPAT, 'UTF-8');

			// Заменим ссылку в кеш на полную картинку
			require_once('inc/filesystem.php');
//			$uri = secure_path(abs_path_from_relative(preg_replace("!^(.+?)/cache/(.+)/\d*x\d*/(.+?)$!", "$1/$2/$3", $uri), $GLOBALS['lcml']['uri']));
			if($main_object = bors()->main_object())
				$uri = secure_path(abs_path_from_relative(preg_replace("!^(.+?)/cache/(.+)/\d*x\d*/(.+?)$!", "$1/$2/$3", $uri), $main_object->url()));

			$data = url_parse($uri);
//			echo $uri; print_d($data); exit();
//			echo $GLOBALS['lcml']['level'];
//			exit(print_r($GLOBALS['lcml']['uri'],true));
//			if(config('is_debug')) { print_d($params); print_d($data); exit(); }

//			if(config('is_debug'))
//				var_dump($data);

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

//var_dump($uri);
//			return $uri;

//			$uri = $hts->normalize_uri($uri, $GLOBALS['lcml']['uri']);

			$data = url_parse($uri);
//			if(config('is_debug')) { echo "Parse '$uri'"; var_dump($data); }

			if(!file_exists($path) && $data['local'])
			{
				$path = $data['local_path'];
				$uri  = $data['uri'];
			}

//			return "$path:$uri:{$GLOBALS['cms']['page_path']}:".str_replace(" ","&nbsp;",print_r($data,true))."<br/>\n";

			if(preg_match('/\w{5,}$/', $data['path']))
				$data['path'] .= '.jpg';

			if(!$data['local'])
			{
				$path = config('sites_store_path')."/{$data['host']}{$data['path']}";

				if(preg_match("!/$!",$path))
					$path .= "index";

				if(!file_exists($path) || filesize($path)==0)
				{
					$c1 = substr($data['host'],0,1);
					$c2 = substr($data['host'],1,1);
					require_once('inc/urls.php');
					$path = config('sites_store_path')."/$c1/$c2/{$data['host']}".translite_path($data['path']);

					if(preg_match("!/$!",$path))
						$path .= "index";
				}

//				return $path;

				if(!file_exists($path) || filesize($path)==0 || !@getimagesize($path))
				{
//					if(preg_match("!(lenta\.ru|pisem\.net|biorobot\.net|compulenta\.ru|ferra\.ru|radikal.ru|postimage.org)!",$uri))
//						$req->setProxy('balancer.endofinternet.net', 3128);

#					if(preg_match("!(ljplus\.ru)!",$uri))
#						$req->setProxy('home.balancer.ru', 3128);

					require_once('inc/http.php');
					$x = http_get_ex($params['url']);
					$content      = $x['content'];
					$content_type = $x['content_type'];

					if(strlen($content) <= 0)
//						return lcml("Zero size error for image '{$uri}'");
						return "<a href=\"{$uri}\">{$uri}</a> <small>[zero size or time out]</small>";

					if(!preg_match("!image!", $content_type))
					{
						debug_hidden_log('images-error', $params['url'].ec(': is not image. ').$content_type); // Это не картинка
//						return lcml("Non-image content type ('$content_type') image ={$uri}= error.");
						return lcml_urls_title($params['url']);
					}

//					if(config('is_debug')) echo "Got content for {$params['url']} to {$path}: ".strlen($content)."\n";

					require_once('inc/filesystem.php');
					mkpath(dirname($path), 0775);
					$fh = fopen($path,'wb');
					fwrite($fh, $content);
					fclose($fh);
					@chmod($path, 0664);

//					$cmd = "wget --header=\"Referer: $uri\" -O \"$path\" \"".html_entity_decode($uri, ENT_COMPAT, 'UTF-8')."\"";
//					return "cmd:$cmd=<br />\n";
//					system($cmd);
				}

				if(file_exists($path) && filesize($path)>0)
				{
					$remote = $uri;
					$uri = str_replace(config('sites_store_path'), config('sites_store_uri'), $path);
					$data['local'] = true;

					$db = new driver_mysql(config('main_bors_db'));

					$id = intval($db->select('images', 'id', array('original_url=' => $remote)));
					if(!$id)
					{
						$db->store('images', 'original_url=\''.addslashes($remote).'\'', array('original_url' => $remote));
						$id = $db->last_id();
					}

					$db->update('images', array('id' => $id), array('local_path' => $path));
				}
			}

			$need_upload = false;

			if($data['local'])
			{
				if(!file_exists($path))
				{
					$GLOBALS['cms']['images'][] = $params['url'];
					$uri  = $GLOBALS['cms']['main_host_uri'].'/cms/templates/default/img/system/not-loaded.png';
					$path = $_SERVER['DOCUMENT_ROOT'].'/cms/templates/default/img/system/not-loaded.png';
					$need_upload = true;
				}

				if($params['noresize'])
					$img_ico_uri  = $uri;
				else
					$img_ico_uri  = preg_replace("!^(http://[^/]+)(.*?)(/[^/]+)$!", "$1/cache$2/{$params['size']}$3", $uri);
//				return "ico=$img_ico_uri; uri=$uri; params=".str_replace(" ","_",print_r($params,true))."<br/>\n";
//				return "_$path, _$uri, _$img_ico_uri<br />\n";
				if(preg_match('!\.[^/+]$!', $uri))
					$img_page_uri = preg_replace("!^(http://.+?)(\.[^\.]+)$!", "$1.htm", $uri);
				else
					$img_page_uri = $uri.'.htm';

				if(defval($params, 'is_direct'))
					$img_page_uri = $uri;

				require_once('HTTP/Request.php');
				$req = new HTTP_Request($img_ico_uri, array('allowRedirects' => true,'maxRedirects' => 2,'timeout' => 4));
				$response = $req->sendRequest();
				if(!empty($response) && PEAR::isError($response))
				{
					sleep(5);
					$response = $req->sendRequest(array('allowRedirects' => true,'maxRedirects' => 2,'timeout' => 8));
				}

				list($width, $height, $type, $attr) = @getimagesize($img_ico_uri);
//				return "__$img_ico_uri:list($width, $height, $type, $attr)__";

				if(!intval($width) || !intval($height))
					return "<a href=\"{$params['url']}\">{$params['url']}</a>";

					/*lcml("Get image [url]{$params['url']}[/url] error [spoiler|details]".
"File: ".__FILE__." line: ".__LINE__."[br]\n".
"uri=_{$uri}_[br]\n".
"path=_{$path}_[br]\n".
"[pre]params=".str_replace(' ', '&nbsp;',print_r($params, true))."[/pre]\n".
"img_ico_uri=_{$img_ico_uri}_[br]\n".
"path=$path[br]\n".
((!empty($response) && PEAR::isError($response))?("responce=".$response->getMessage()."\n"):'').
"[/spoiler]\n");*/

//				if(!empty($GLOBALS['main_uri']))
//					$hts->nav_link($GLOBALS['main_uri'], $uri);
//				require_once("funcs/images/fill.php");
//				fill_image_data($uri);

//				return "==={$params['description']}===";

				if(empty($params['description']))
					$params['description'] = "";

				if($need_upload)
				{
					$params['description'] .= <<<__EOT__
<br />
<form action="{$GLOBALS['cms']['main_host_uri']}/admin/upload.php" method="post" enctype="multipart/form-data">
{$params['url']}<br />
<input type="hidden" name="upload_names[]" value="{$params['url']}">
<input type="file" size="10" name="upload_file[]">
<input type="submit" value="Load">
<input type="hidden" name="page" value="{$GLOBALS['main_uri']}">
</form>
__EOT__;
				}

				$description = stripslashes(!empty($params['description']) ? "<div style=\"text-align: center\"><small>".lcml($params['description'])."</small></div>" : '');

//				print_d($params); exit();

				$a_href_b = "";
				$a_href_e = "";

				if(empty($params['nohref']))
				{
					$a_href_b = "<a href=\"$img_page_uri\">";
					$a_href_e = "</a>";
				}

				$styles = array();
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

				$out = '';

				if(@$params['border'])
				{
					if($width > 640)
						$out .= "<div class=\"clear\">&nbsp;</div>\n";

					$styles[] = 'box';
				}

				$out .= '<div class="'.join(' ', $styles)."\" style=\"width:".($width)."px;".(!$description? "height:".($height)."px" : "").";\">{$a_href_b}<img src=\"$img_ico_uri\" width=\"$width\" height=\"$height\" alt=\"\" />{$a_href_e}";
				if($description)
					$out .= "<div style=\"font-size: xx-small;\">".lcml($description, array('html'=>'safe'))."</div>";
				$out .= '</div>';

				return $out;
			}
		}
		return "<a href=\"{$params['url']}\">{$params['url']}</a>";
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
