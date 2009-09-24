<?php
@include_once("localization/russian-{$GLOBALS['cms']['charset_u']}.php");

function tr($txt)
{
	return empty($GLOBALS['cms']['lang'][$txt]) ? $txt : $GLOBALS['cms']['lang'][$txt];
}

function ec($txt)
{
	$charset = config('internal_charset', 'utf-8');
	if($charset == 'utf-8')
		return $txt;

	if($charset == 'koi8-r')
		$txt = str_replace(array('«','»'), array('&laquo;','&raquo;'), $txt);

	return iconv('utf-8', $charset.'//TRANSLIT', $txt);
}

function dc($txt, $charset_from = NULL, $charset_to = NULL)
{
	if(!$charset_to)
		$charset_to = 'utf-8';

	if(!$charset_from)
		$charset_from = config('internal_charset', 'utf-8');

	if($charset_from == $charset_to)
		return $txt;

	if($charset_to == 'koi8-r')
		$txt = str_replace(array('«','»'), array('&laquo;','&raquo;'), $txt);

	return iconv($charset_from, $charset_to, $txt);
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
