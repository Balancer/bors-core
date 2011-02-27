<?php

//	Класс извлечения контента сайта fresher.ru

class bors_external_site_fresher extends bors_object
{
	static function parse($html)
	{
		$dom = new DOMDocument('1.0', 'UTF-8');
		$dom->loadHTML($html);
		$xpath = new DOMXPath($dom);

		$main = $xpath->query('//div[@class="tip conttip"]')->item(0);

		$main->removeChild($dom->getElementById('sharebar'));
		$main->removeChild($dom->getElementById('sharebarx'));

		foreach(array(
			'//div[@class="more link"]',
			'//p[@class="link sects"]',
			'//div[@class="tip conttip"]/h2/span',
			'//div[@class="tip linkss"]/p[@class="comments"]',
		) as $query)
			foreach($xpath->query($query) as $node)
				$node->parentNode->removeChild($node);

		$out = bors_lib_bb::from_dom($main)."\n";

		$more = $xpath->query('//div[@class="tip linkss"]')->item(0);
		$out .= "\n\n// ".trim(bors_lib_bb::from_dom($more))."\n";

		return trim(preg_replace("/\n{3,}/", "\n\n", $out));
	}
}
