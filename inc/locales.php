<?php
@include_once("localization/russian-{$GLOBALS['cms']['charset_u']}.php");

function tr($txt)
{
	return empty($GLOBALS['cms']['lang'][$txt]) ? $txt : $GLOBALS['cms']['lang'][$txt];
}

function ec($txt)
{
	return iconv('utf-8', config('charset', 'utf-8').'//translit', $txt);
}

function dc($txt)
{
	return iconv("{$GLOBALS['cms']['charset']}", 'utf-8', $txt);
}
