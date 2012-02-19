<?php

//	Класс извлечения контента сайта fresher.ru

class bors_external_fresher extends bors_object
{
	static function parse($html, $limit=1500)
	{
		$html = preg_replace('!<script>[^>]*function.*?</script>!s', '', $html);

//		$html = preg_replace('!<div id="(\w+)">([^<]+?)</div>\s*<p><script[^>]*>([^>]*jwplayer.*?)</script>!s', "[html_div id=$1]$2[/html_div]\n[bors3rdp_js]js/jwplayer.js[/bors3rdp_js]\n[javascript]$3[/javascript]", $html);

		$html = preg_replace('!<div id="(\w+)">([^<]+?)</div>\s*<p><script[^>]*>([^>]*jwplayer.*?)</script>!s', "<div><i>Видео смотрите по оригинальной ссылке [BalaBOT]</i></div>", $html);

		$dom = new DOMDocument('1.0', 'UTF-8');
		if(!@$dom->loadHTML($html))
			return NULL;

		$xpath = new DOMXPath($dom);

		$main = $xpath->query('//div[@class="tip conttip"]')->item(0);

		foreach(array('sharebar', 'sharebarx') as $id)
			if($el = $dom->getElementById($id))
				$main->removeChild($el);

		$tags = array();
		foreach($xpath->query('//p[@class="link sects"]/span/a') as $node)
			if($tag = $node->nodeValue)
				$tags[] = $tag;

		foreach(array(
			'//style',
			'//div[@class="more link"]',
			'//p[@class="link sects"]',
			'//div[@class="tip conttip"]/h2/span',
			'//div[@class="tip conttip"]/div[@class="rates"]',
			'//div[@class="tip linkss"]/p[@class="comments"]',
			'//style',
//			'//script',
		) as $query)
			foreach($xpath->query($query) as $node)
				$node->parentNode->removeChild($node);

		$title = trim($xpath->query('//div[@class="tip conttip"]/h2')->item(0)->nodeValue);

		$bb_code = bors_lib_bb::from_dom($main)."\n";

		$more = $xpath->query('//div[@class="tip linkss"]')->item(0);
		$bb_code = trim("$bb_code\n\n// ".trim(bors_lib_bb::from_dom($more)));

		if(preg_match('/\[embed/', $bb_code))
			$bb_code = preg_replace('!\[html_video.+?\[/html_video\]!s', '', $bb_code);

		$len = bors_strlen($bb_code);
		$bb_code = clause_truncate_ceil($bb_code, $limit);
		$bb_code = bors_close_bbtags($bb_code);
		if($len >= $limit)
//			$bb_code .= "\n\n[url={$url}]".ec('… дальше »»»[/url]');
			$bb_code .= "\n\n".ec('… дальше »»»');

		return compact('title', 'bb_code', 'tags');
	}
}
