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

	/*
		Исправляем некорректные символы utf-8. Источник: http://magp.ie/2011/01/06/remove-non-utf8-characters-from-string-with-php/
		Использовать iconv //translit или //ignore нельзя, на некоторых системах не работает
	*/

	static function utf8_fix($string)
	{
		//reject overly long 2 byte sequences, as well as characters above U+10000 and replace with ?
		$string = preg_replace('/[\x00-\x08\x10\x0B\x0C\x0E-\x19\x7F]'
				.'|[\x00-\x7F][\x80-\xBF]+'
				.'|([\xC0\xC1]|[\xF0-\xFF])[\x80-\xBF]*'
				.'|[\xC2-\xDF]((?![\x80-\xBF])|[\x80-\xBF]{2,})'
				.'|[\xE0-\xEF](([\x80-\xBF](?![\x80-\xBF]))|(?![\x80-\xBF]{2})|[\x80-\xBF]{3,})/S',
			'?', $string);

		//reject overly long 3 byte sequences and UTF-16 surrogates and replace with ?
		$string = preg_replace('/\xE0[\x80-\x9F][\x80-\xBF]'
				.'|\xED[\xA0-\xBF][\x80-\xBF]/S'
			,'?', $string);

		return $string;
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
