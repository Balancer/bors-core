<?php

function bors_truncate($string, $length = 80, $etc = NULL, $break_words = false, $middle = false)
{
	if(is_null($etc))
		$etc = ec('…');

	return truncate($string, $length, $etc, $break_words, $middle);
}

function truncate($string, $length = 80, $etc = NULL, $break_words = false, $middle = false)
{
    if($length == 0)
        return '';

	if(is_null($etc))
		$etc = ec('…');

    if(bors_strlen($string) <= $length)
        return $string;

	$length -= min($length, bors_strlen($etc));
	if(!$break_words && !$middle)
	{
		$string = preg_replace('/\s+?(\S+)?$/', '', bors_substr($string, 0, $length+1));
	}

	if(!$middle)
	{
   		return bors_substr($string, 0, $length) . $etc;
	}
	else
	{
	    return bors_substr($string, 0, $length/2) . $etc . bors_substr($string, -$length/2);
	}
}
