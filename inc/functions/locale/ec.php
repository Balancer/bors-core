<?php

function ec($txt, $charset_from = 'utf-8') // utf-8 или указанную во внутреннюю кодировку
{
	$charset_to = config('internal_charset', 'utf-8');

	if(strcasecmp($charset_from, $charset_to) == 0)
		return $txt;

	if($charset_to == 'koi8-r')
		$txt = str_replace(array('«','»','–'), array('&laquo;','&raquo;','&mdash;'), $txt);

	return @iconv($charset_from, $charset_to.'//TRANSLIT', $txt);
}
