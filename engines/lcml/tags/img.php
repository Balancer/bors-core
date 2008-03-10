<?php

require_once('inc/urls.php');

	function lt_img($params) 
	{ 
//		print_d($params); exit();

		if(empty($params['size']))
			$params['size'] = '468x468';

		if(!empty($params['url']))
		{
			$path = NULL;
			@$uri = html_entity_decode($params['url'], ENT_COMPAT, 'UTF-8');

			// Заменим ссылку в кеш на полную картинку
			$uri = secure_path(abs_path_from_relative(preg_replace("!^(.+?)/cache/(.+)/\d*x\d*/(.+?)$!", "$1/$2/$3", $uri), $GLOBALS['lcml']['uri']));

			$data = url_parse($uri);
//			echo $GLOBALS['lcml']['level'];
//			exit(print_r($GLOBALS['lcml']['uri'],true));
//			exit(print_r($data,true));

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

//			exit($uri);

//			$uri = $hts->normalize_uri($uri, $GLOBALS['lcml']['uri']);

			$data = url_parse($uri);

			if(!file_exists($path) && $data['local'])
			{
				$path = $data['local_path'];
				$uri  = $data['uri'];
			}

//			return "$path:$uri:{$GLOBALS['cms']['page_path']}:".str_replace(" ","&nbsp;",print_r($data,true))."<br/>\n";

			if(!$data['local'])
			{
				$path = $GLOBALS['cms']['sites_store_path']."/{$data['host']}{$data['path']}";
			
				if(preg_match("!/$!",$path))
					$path .= "index";

				if(!file_exists($path) || filesize($path)==0)
				{
					$c1 = substr($data['host'],0,1);
					$c2 = substr($data['host'],1,1);
					require_once('funcs/modules/uri.php');
					$path = $GLOBALS['cms']['sites_store_path']."/$c1/$c2/{$data['host']}".translite_path($data['path']);

					if(preg_match("!/$!",$path))
						$path .= "index";
				}

//				exit($path);

				if(!file_exists($path) || filesize($path)==0)
				{
					require_once('HTTP/Request.php');
					$req =& new HTTP_Request($params['url'], array(
						'allowRedirects' => true,
						'maxRedirects' => 2,
						'timeout' => 5,
					));
					
//					exit("down {$params['url']}");
						
					$req->addHeader('Content-Encoding', 'gzip');
					$req->addHeader('Referer', $params['url']);

//					if(preg_match("!(lenta\.ru|pisem\.net|biorobot\.net|compulenta\.ru|ferra\.ru)!",$uri))
//						$req->setProxy('home.balancer.ru', 3128);

//					return "=$path=<br />\n";

					$response = $req->sendRequest();

					if(!empty($response) && PEAR::isError($response)) 
						return "Download image =$uri= error: ".$response->getMessage();

					$data = $req->getResponseBody();
					if(strlen($data) <= 0)
						return lcml("Zero size image ={$uri}= error.");

					$content_type = $req->getResponseHeader('Content-Type');
					if(!preg_match("!image!",$content_type))
						return $params['url'];
//						return lcml("Non-image content type ('$content_type') image ={$uri}= error.");

					require_once('funcs/filesystem_ext.php');
					mkpath(dirname($path));
					$fh = fopen($path,'wb');
					fwrite($fh, $data);
					fclose($fh);
//					$cmd = "wget --header=\"Referer: $uri\" -O \"$path\" \"".html_entity_decode($uri, ENT_COMPAT, 'UTF-8')."\"";
//					return "cmd:$cmd=<br />\n";
//					system($cmd);
				}

				if(file_exists($path) && filesize($path)>0)
				{
					$remote = $uri;
					$uri = str_replace($GLOBALS['cms']['sites_store_path'], $GLOBALS['cms']['sites_store_uri'], $path);
					$data['local'] = true;
					
					$db = &new driver_mysql('BORS');
					
					$id = intval($db->select('images', 'id', array('original_url=' => $remote)));
					if(!$id)
					{
						$db->store('images', 'original_url=\''.addslashes($remote).'\'', array('original_url' => $remote));
						$id = $db->last_id();
					}

					$db->update('images', 'id='.$id, array('local_path' => $path));
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

				$img_ico_uri  = preg_replace("!^(http://[^/]+)(.*?)(/[^/]+)$!", "$1/cache$2/{$params['size']}$3", $uri);
//				return "ico=$img_ico_uri; uri=$uri; params=".str_replace(" ","_",print_r($params,true))."<br/>\n";
//				return "_$path, _$uri, _$img_ico_uri<br />\n";
				if(preg_match('!\.[^/+]$!', $uri))
					$img_page_uri = preg_replace("!^(http://.+?)(\.[^\.]+)$!", "$1.htm", $uri);
				else
					$img_page_uri = $uri.'.htm';
//				return "_$local_uri<br />_$img_ico_uri<br />";

				require_once('HTTP/Request.php');
				$req =& new HTTP_Request($img_ico_uri, array('allowRedirects' => true,'maxRedirects' => 2,'timeout' => 4));
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
				require_once("funcs/images/fill.php");
				fill_image_data($uri);
			   
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

				$description = stripslashes(!empty($params['description']) ? "<div align=\"center\"><small>".lcml($params['description'])."</small></div>" : '');

//				print_r($params); exit();

				$a_href_b = "";
				$a_href_e = "";

				if(empty($params['nohref']))
				{
					$a_href_b = "<a href=\"$img_page_uri\">";
					$a_href_e = "</a>";
				}

//				$out = <<<__EOT__
//{$params['_align_b']}<table class="box" style="width: {$width}px;" cellSpacing="0" cellPadding="2"><tr><td width="$width">$a_href_b<img src="$img_ico_uri" width="$width" height="$height" border="0" />$a_href_e</td></tr>$description</table>{$params['_align_e']}
//__EOT__;
				$out = "{$params['_align_b']}$a_href_b<img src=\"$img_ico_uri\" width=\"$width\" height=\"$height\" border=\"0\">$a_href_e<div style=\"font-size: xx-small;\">".lcml($description, array('html'=>'safe'))."</div>{$params['_align_e']}";

//		$out .= "<!-- params ".print_r($params,true)." -->";

				return $out;
			}
		}
	}
