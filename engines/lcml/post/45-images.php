<?php

define('LCML_IMG_TIMEOUT', ($t = ini_get('max_execution_time')) ? $t*3/4 : 30);

function lcml_images($txt, $lcml)
{
	if(!$lcml->is_tag_enabled('img'))
		return $txt;

	if($lcml->is_timeout(LCML_IMG_TIMEOUT))
		return $txt;

	$n=50;
	while(preg_match("!\[([https?://\w\.\-\+%_/:&\?=#]+\.(jpg|jpeg|gif|png|sjpg))([^\]]*)\]!i", $txt, $m) && $n-->0)
		$txt = str_replace($m[0], $lcml->parse("[img \"{$m[1]}\" noflow {$m[3]}]"), $txt);

	while(preg_match("!^[\s￼ ]*(https?://[^\s\?]+\.(jpg|png|gif|jpeg|sjpg))\s*$!im", $txt, $m)
			&& !$lcml->is_timeout(LCML_IMG_TIMEOUT)
	)
	{
		try
		{
			$image_url = $m[1];
			$ud = parse_url($image_url);
			$txt = str_replace($m[0], lt_img([
				'orig' => $image_url,
				'url' => $image_url,
				'align' => 'left',
				'flow' => 'noflow',
				'no_lcml_description' => true,
//				'href' => $image_url,
				'self' => $lcml->p('self'),
				'description' => "<a href=\"{$image_url}\" rel=\"nofollow\">".basename($image_url)."</a> @ <a href=\"http://{$ud['host']}\" rel=\"nofollow\">{$ud['host']}</a> [<a href=\"%IMAGE_PAGE_URL%\">кеш</a>]",
				'border' => true,
			]), $txt);
		}
		catch(Exception $e)
		{
			bors_debug::syslog('error-lcml', sprintf(_("Image '%s' single tag error: "), $image_url).$e->getMessage());
		}
	}

	$n=50;
	while(preg_match("!\[([\w/]+.(jpg|jpeg|gif|png|sjpg))([^\]]*)\]!i", $txt, $m) && $n-->0)
		$txt = str_replace($m[0], lcml("[img \"{$m[1]}\" noflow {$m[3]}]"), $txt);

	return $txt;
}
