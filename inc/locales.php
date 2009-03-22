<?php
@include_once("localization/russian-{$GLOBALS['cms']['charset_u']}.php");

function tr($txt)
{
	return empty($GLOBALS['cms']['lang'][$txt]) ? $txt : $GLOBALS['cms']['lang'][$txt];
}

function ec($txt)
{
	$charset = config('charset', 'utf-8');
	if($charset == 'utf-8')
		return $txt;

	return iconv('utf-8', $charset.'//translit', $txt);
}

function dc($txt, $charset_from = NULL, $charset_to = NULL)
{
	if(!$charset_to)
		$charset_to = config('charset_internal', 'utf-8');

	if(!$charset_from)
		$charset_from = config('charset', 'utf-8');

	if($charset_from == $charset_to)
		return $txt;

	if($charset_to == 'koi8-r')
		echo $txt = str_replace(array('«','»'), array('&laquo;','&raquo;'), $txt);

	return iconv($charset_from, $charset_to, $txt);
}
