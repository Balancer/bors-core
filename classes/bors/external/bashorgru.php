<?php

class bors_external_bashorgru extends bors_object
{
	static function content_extract($url)
	{
		$html = bors_lib_http::get($url);
		$dom = new DOMDocument('1.0', 'utf-8');
		$dom->loadHTML($html);
		$xpath = new DOMXPath($dom);

		$body = $xpath->query("//div[@class='q']/div")->item(1);
		$body = iconv('utf-8', 'cp1251', $body->nodeValue); // Жесть какая-то. Хак.
		$bbcode = preg_replace("/^\s+$/m", "", $body);
		$bbcode = preg_replace("/\n{2,}/", "\n\n", $bbcode);
		$len = bors_strlen($bbcode);
		$bbcode = clause_truncate_ceil($bbcode, 1500);
		if($len >= 1500)
			$bbcode .= "\n\n[url={$url}]".ec('… дальше »»»[/url]');

		$bbshort = "[b][url={$url}]bash.org.ru[/url][/b]

{$bbcode}
// ".ec("Источник: ").bors_external_feeds_entry::url_host_link($url);

		return compact('bbshort');
	}
}
