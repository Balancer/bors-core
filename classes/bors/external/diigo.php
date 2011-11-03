<?php

class bors_external_diigo extends bors_object
{
	static function parse($data)
	{
//		var_dump($data);
		extract($data);

		$tags = array();
		if(preg_match_all("!rel='tag'>(.+?)</a>!u", $text, $matches))
			foreach($matches[1] as $m)
				$tags[] = common_keyword::loader(str_replace('_', ' ', $m))->synonym_or_self()->title();

//		var_dump($tags); exit();

		$bbcode = "Новая ссылка на [http://www.diigo.com/user/balancer73 diigo.com]: [b][i][url=$link]$title[/url][/i][/b]\n$link";

		if(preg_match('!^<p>(.*?)</p>!', trim($text), $m))
			$bbcode = "$bbcode\n\n{$m[1]}";

		return array(
			'title' => $title,
			'tags' => $tags,
			'text' => $text,
			'bb_code' => $bbcode,
			'markup' => 'bors_markup_lcmlbbh',
		);
	}
}
