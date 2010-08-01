<?php

class bors_external_search
{
	static function query_extract($url)
	{
//		if(pre)

		static $google_img = array('!&prev=/images%3Fq%3D(.+?)%26!', '$q = urldecode(urldecode($q[1]));');
		static $w1251_search = '$q = iconv("windows-1251", "utf-8", urldecode($q[1]));';

		$search_engine_domains = array(
			'go.mail.ru' => array('/q=([^&]+)/', $w1251_search),
			'google.co.il' => 'q',
			'google.com.pe' => 'q',
			'google.com.ua' => 'q',
			'google.ee' => 'q',
			'google.it' => 'q',
			'google.pt' => 'q',
			'ie.search.msn.com' => 'q',
			'images.yandex.ru' => 'text',
			'images.google.cl' => $google_img,
			'images.google.cn' => $google_img,
			'images.google.co.uk' => $google_img,
			'images.google.com' => $google_img,
			'images.google.com.by' => $google_img,
			'images.google.com.jp' => $google_img,
			'images.google.com.ua' => $google_img,
			'images.google.dk' => $google_img,
			'images.google.fr' => $google_img,
			'images.google.kz' => $google_img,
			'images.google.hu' => $google_img,
			'images.google.ru' => $google_img,
			'images.google.se' => $google_img,
			'm.yandex.ru' => 'query',
			'nigma.ru' => 's',
			'nova.rambler.ru' => 'query',
			'poisk.ru' => array('/text=([^&]+)/', $w1251_search),
			'search.conduit.com' => 'q',
			'search.icq.com' => 'q',
			'search.live.com' => 'q',
			'search.qip.ru' => 'query',
			'search.ukr.net' => 'search_query',
			'search.yahoo.com' => 'p',
			'us.yhs.search.yahoo.com' => 'p',
			'www.bing.com' => 'q',
			'www.google.az' => 'q',
			'www.google.ca' => 'q',
			'www.google.cz' => 'q',
			'www.google.com' => 'q',
			'www.google.com.br' => 'q',
			'www.google.com.by' => 'q',
			'www.google.com.ua' => 'q',
			'www.google.com.uk' => 'q',
			'www.google.de' => 'q',
			'www.google.gr' => 'q',
			'www.google.kz' => 'q',
			'www.google.lv' => 'q',
			'www.google.md' => 'q',
			'www.google.pl' => 'q',
			'www.google.ru' => 'q',
			'www.nigma.ru' => 's',
			'www.yandex.ru' => array('/text=([^&]+)/', $w1251_search),
			'yandex.ru' => 'text',
			'yandex.ua' => 'text',
		);

		$data = parse_url($url);

		if(!($se = @$search_engine_domains[$data['host']]))
			return false;

		if(is_array($se))
		{
			$re = $se[0];
			$func = $se[1];
		}
		else
		{
			$re = "/(\?|&){$se}=([^&]+)/";
			$func = '$q = urldecode($q[2]);';
		}

		if(!preg_match($re, $url, $q))
			return false;

		eval($func);
//		echo "\t\tSearch $ref_domain: '$q' -> $uri".($obj ? " [{$obj}({$obj->page()})]":'')."\n";

		return $q;
	}
}
