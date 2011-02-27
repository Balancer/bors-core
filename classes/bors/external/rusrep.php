<?php

//	Класс извлечения статей http://rusrep.ru
//	Пример: http://rusrep.ru/article/2011/01/26/report
//			via http://balancer.ru/g/p2386368

class bors_external_rusrep extends bors_object
{
	static function parse($html, $base_url)
	{
		$dom = new DOMDocument();
		$dom->loadHTML(str_replace("\r\n", "\n", $html));
		$xpath = new DOMXPath($dom);

		$title = trim($xpath->query('//div[@class="preview"]/h1')->item(0)->nodeValue);

		$tags = array();
		foreach($xpath->query('//div[@class="info"]/div[@class="tag"]/a') as $node)
			if($tag = trim($node->nodeValue))
				$tags[] = $tag;

		$announce = trim($xpath->query('//div[@class="preview"]/div[@class="note"]')->item(0)->nodeValue);
/*
		$x = $xpath->query('/div[@class="preview"]/div[@class="note"]')->item(0);
		$text = $x->nodeValue;
		$i = $doc->createElement("i");
		$i->appendChild($doc->createTextNode($text)); 
		$x->parentNode->replaceChild($i, $x);
*/
		echo "?";
		foreach($xpath->query('//a/img') as $img)
		{
			$a = $img->parentNode;
			if($img->getAttribute('src') == $a->getAttribute('href'))
				$a->parentNode->replaceChild($img, $a);
		}

		$body = $dom->getElementById('font_size');

//		$main->removeChild($dom->getElementById('sharebarx'));

		foreach(array(
			'//div[@style="display: none;"]',
		) as $query)
			foreach($xpath->query($query) as $node)
				$node->parentNode->removeChild($node);


		$bb_code = bors_lib_bb::from_dom($body, $base_url)."\n";

//		$more = $xpath->query('//div[@class="tip linkss"]')->item(0);
		$bb_code = trim("[h]{$title}[/h]

[i]{$announce}[/i]\n\n".trim($bb_code));

		return compact('title', 'bb_code', 'tags');
	}
}
