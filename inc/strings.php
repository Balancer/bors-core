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

    if (strlen($string) > $length)
	{
        $length -= min($length, strlen($etc));
        if (!$break_words && !$middle)
		{
            $string = preg_replace('/\s+?(\S+)?$/', '', substr($string, 0, $length+1));
        }
        if(!$middle)
		{
            return substr($string, 0, $length) . $etc;
        }
		else
		{
            return substr($string, 0, $length/2) . $etc . substr($string, -$length/2);
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
