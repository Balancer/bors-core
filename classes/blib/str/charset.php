<?php

class blib_str_charset
{
	static function detect($str)
	{
		// Пока тупо различаем utf-8 и cp-1251.
		// В будущем — изучить http://habrahabr.ru/post/107945/
		if(preg_match("//u", $str))
			return 'utf-8'; // Только в нижнем регистре и в таком формате.

		return 'windows-1251';
	}

	static function decode($str)
	{
		$charset = self::detect($str);
		if($charset != strtolower($dcs = ini_get('default_charset')))
			$str = iconv($charset, $dcs, $str);

		return $str;
	}

	function __dev()
	{
		var_dump(
			self::decode(urldecode('http://ru.wikipedia.org/wiki/%C8%ED%E4%E5%E9%F1%EA%E8%E5_%E2%EE%E9%ED%FB')),
			self::decode(urldecode('http://ru.wikipedia.org/wiki/%D0%98%D0%BD%D0%B4%D0%B5%D0%B9%D1%81%D0%BA%D0%B8%D0%B5_%D0%B2%D0%BE%D0%B9%D0%BD%D1%8B')),
			self::decode(urldecode('http://ru.wikipedia.org/wiki/Индейские_войны'))
		);
	}
}
