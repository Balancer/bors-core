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

	return $s5;
}

/**
	То же, что и функция sklon(), но пишет и само число.
	<p>Всего <?= sklonn($amount, 'штука,штуки,штук')?> на общую сумму <b><?=$sum_rur?> руб.</b></p>
*/

function sklonn($n, $s1, $s2=NULL, $s5=NULL)
{
	if($s2 === NULL)
		list($s1, $s2, $s5) = explode(',', $s1);

    $ns=intval(substr($n,-1));
    $n2=intval(substr($n,-2));

    if($n2>=10 && $n2<=19) return $n.' '.$s5;
    if($ns==1) return $n.' '.$s1;
    if($ns>=2&&$ns<=4) return $n.' '.$s2;

    return $n.' '.$s5;
}

bors_function_include('text/truncate');

function stripq($text) { return str_replace('\\"', '"', $text); }

function bors_hypher($string)
{
	if(is_global_key('hypher-cache', $string))
		return global_key('hypher-cache', $string);

	if(preg_match('/^[a-zA-Z0-9\-\/ ]*$/', $string))
		return set_global_key('hypher-cache', $string, $string);

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
	return set_global_key('hypher-cache', $string, $result);
}

bors_function_include('natural/bors_plural');
bors_function_include('natural/bors_unplural');

function bors_str_cat($string1, $string2, $explode_delimiter = ',', $join_delimiter = ', ')
{
	$a1 = array_map('trim', explode($explode_delimiter, $string1));
	$a2 = array_map('trim', explode($explode_delimiter, $string2));
	$a = array_filter(array_merge($a1, $a2));
	sort($a);
	return join($join_delimiter, $a);
}

bors_function_include('string/bors_starts_with');
bors_function_include('string/bors_ends_with');

function bors_entity_decode($string)
{
	return html_entity_decode($string, ENT_COMPAT, config('internal_charset'));
}

function bors_comma_join($s1, $s2 = NULL)
{
	if(!is_array($s1))
		$s1 = preg_split('/\s*[,;]+\s*/', $s1);

	if($s2)
	{
		if(!is_array($s2))
			$data = preg_split('/\s*[,;]+\s*/', $s2);
	}
	else
		$s2 = array();

	return join(', ', array_merge($s1, $s2));
}
