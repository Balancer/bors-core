<?php
	function lcml_images($txt)
	{
		if(lcml_tag_disabled('img'))
			return $txt;

		$n=50;
		while(preg_match("!\[([https?://\w\.\-\+%_/:&\?=#]+\.(jpg|jpeg|gif|png|sjpg))([^\]]*)\]!ie", $txt, $m) && $n-->0)
			$txt = str_replace($m[0], lcml("[img \"{$m[1]}\" noflow {$m[3]}]"), $txt);

		$n=50;
		while(preg_match("!^\S*(https?://\S+\.(jpg|png|gif|jpeg|sjpg))\s*$!ime", $txt, $m) && $n-->0)
		{
			$image_url = $m[1];
			$ud = parse_url($image_url);
			$txt = str_replace($m[0], lt_img(array(
					'orig' => $image_url,
					'url' => $image_url,
					'align' => 'left',
					'flow' => 'noflow',
					'no_lcml_description' => true,
					'href' => $image_url,
					'description' => "<a href=\"{$image_url}\">".basename($image_url)."</a> @ <a href=\"http://{$ud['host']}\">{$ud['host']}</a> [<a href=\"%IMAGE_PAGE_URL%\">кеш</a>]",
					'border' => true,
				)), $txt);
		}

		$n=50;
		while(preg_match("!\[([\w/]+.(jpg|jpeg|gif|png|sjpg))([^\]]*)\]!ie", $txt, $m) && $n-->0)
			$txt = str_replace($m[0], lcml("[img \"{$m[1]}\" noflow {$m[3]}]"), $txt);

		return $txt;
	}
