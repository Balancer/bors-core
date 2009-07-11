<?php

function remove_last_slash($s)
{
    return $s[strlen($s)-1]=='/' ? substr($s,0,-1) : $s;
}

function sklon($n, $s1, $s2=NULL, $s5=NULL) // 1 нож 2 ножа 5 ножей
{
	if($s2 === NULL)
		list($s1, $s2, $s5) = explode(',', $s1);
			
   	$ns=intval(substr($n,-1));
		$n2=intval(substr($n,-2));

    if($n2>=10 && $n2<=19) return $s5;
   	if($ns==1) return $s1;
    if($ns>=2&&$ns<=4) return $s2;
   	if($ns==0 || $ns>=5) return $s5;
	return "Неизвестная пара '$n $s1'! Пожалуйста, сообщи об этой ошибке администратору!";
}

function truncate($string, $length = 80, $etc = '...', $break_words = false, $middle = false)
{
    if ($length == 0)
        return '';

    if(bors_strlen($string) > $length)
	{
        $length -= min($length, bors_strlen($etc));
        if (!$break_words && !$middle)
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
	else
	{
        return $string;
    }
}

function stripq($text) { return str_replace('\\"', '"', $text); }

function bors_hypher($string)
{
	global $bors_3rd_glob_hypher;
	if(empty($bors_3rd_glob_hypher))
	{
		require_once 'phphypher/hypher.php';
		$bors_3rd_glob_hypher = hypher_load(BORS_3RD_PARTY.'/phphypher/hyph_ru_RU.conf');
	}

	$mb_enc = ini_get('mbstring.internal_encoding');
	ini_set('mbstring.internal_encoding', 'windows-1251');
	$result = iconv('windows-1251', 'utf-8', hypher($bors_3rd_glob_hypher, iconv('utf-8', 'windows-1251//IGNORE', $string)));
	ini_set('mbstring.internal_encoding', $mb_enc);
	return $result;
}

if(function_exists('mb_strtolower')) 
{
	eval('function bors_upper($str) { return mb_strtoupper($str); }');
	eval('function bors_lower($str) { return mb_strtolower($str); }');
	eval('function bors_strlen($str) { return mb_strlen($str); }');
	eval('function bors_substr($str, $start, $length=NULL) { return is_null($length) ? mb_substr($str, $start) : mb_substr($str, $start, $length); }');
}
else
{
	eval('function bors_lower($str) { return strtolower($str); }');
	eval('function bors_upper($str) { return strtoupper($str); }');
	eval('function bors_strlen($str) { return strlen($str); }');
	eval('function bors_substr($str, $start, $length=NULL) { return is_null($length) ? substr($str, $start) : substr($str, $start, $length); }');
}
