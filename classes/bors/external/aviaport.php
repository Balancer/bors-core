<?php

class bors_external_aviaport extends bors_object
{
	static function content_extract($url)
	{
		$html = bors_lib_http::get($url);
		$dom = new DOMDocument('1.0', 'utf-8');
		$dom->loadHTML($html);
		$xpath = new DOMXPath($dom);

		$title = $xpath->query("//div[@id='main-wide']/h3")->item(0)->nodeValue;
		$body  = $xpath->query("//div[@id='main-wide']/div[@class='justify']")->item(0)->nodeValue;

		$bbcode = preg_replace("/^\s+$/m", "", trim($body));
		$bbcode = preg_replace("/\n{2,}/", "\n\n", $bbcode);
		$len = bors_strlen($bbcode);
		$bbcode = clause_truncate_ceil($bbcode, 1500);
		if($len >= 1500)
			$bbcode .= "\n\n[url={$url}]".ec('… дальше »»»[/url]');

//		var_dump($title);		var_dump($body);		exit();

		$bbshort = "[b][url={$url}]{$title}[/url][/b]

{$bbcode}
// ".ec("Источник: ").bors_external_feeds_entry::url_host_link($url);

		return compact('bbshort');
	}
}
