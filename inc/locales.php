<?php

// @include_once("localization/russian-".config('internal_charset', 'utf-8').".php");

function tr($txt)
{
	return empty($GLOBALS['cms']['lang'][$txt]) ? $txt : $GLOBALS['cms']['lang'][$txt];
}

bors_function_include('locale/ec');

function dc($txt, $charset_to = NULL) // внутренняя кодировка в UTF-8 или в указанную.
{
	if(!$charset_to)
		$charset_to = 'utf-8';

	$charset_from = config('internal_charset', 'utf-8');

	if(strcasecmp($charset_from, $charset_to) == 0)
		return $txt;

	if($charset_to == 'koi8-r' || $charset_to == 'cp866')
		$txt = str_replace(
			array('«','»','„','“','©', '–'),
			array('&laquo;','&raquo;','&bdquo;','&ldquo;','&copy;','&mdash;'),
			$txt);

	$txt = iconv($charset_from, $charset_to.'//IGNORE', $txt);
//	if($charset_to == 'utf-8' || $charset_to == 'windows-1251')
//		$txt = str_replace(array('&laquo;','&raquo;', '&copy;', '&mdash;'), array('«','»','©', '–'), $txt);

	return $txt;
}

function array_iconv($from_charset, $to_charset, $array)
{
	if(is_array($array))
	{
		foreach($array as $k => $v)
			if(is_array($v))
				$array[$k] = array_iconv($from_charset, $to_charset, $v);
			else
				$array[$k] = iconv($from_charset, $to_charset, $v);
	}
	else
		$array = iconv($from_charset, $to_charset, $array);

	return $array;
}

function u_lower($text)
{
	if(($ics = config('internal_charset')) != ($scs = config('system_charset')))
		return iconv($scs, $ics.'//IGNORE', strtolower(iconv($ics, $scs.'//IGNORE', $text)));

	else return strtolower($text);
}
