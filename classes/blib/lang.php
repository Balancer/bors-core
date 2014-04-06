<?php

class blib_lang
{
	static function translate($text)
	{
		$lang = config('lang.ua');
		if(empty($lang[$text]))
		{
//			if(preg_match('/[а-яА-ЯёЁ]/u', $text) && !in_array($text, explode(' ', 'РД …')))
//				bors_debug::syslog('translations', "Need translate '{$text}'");
		}
		else
			$text = $lang[$text];

		return $text;
	}
}
