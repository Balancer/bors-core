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

bors_transitional::function_include('text/truncate');

function stripq($text) { return str_replace('\\"', '"', $text); }

bors_transitional::function_include('natural/bors_plural');

function bors_str_cat($string1, $string2, $explode_delimiter = ',', $join_delimiter = ', ')
{
	$a1 = array_map('trim', explode($explode_delimiter, $string1));
	$a2 = array_map('trim', explode($explode_delimiter, $string2));
	$a = array_filter(array_merge($a1, $a2));
	sort($a);
	return join($join_delimiter, $a);
}

bors_transitional::function_include('string/bors_starts_with');
bors_transitional::function_include('string/bors_ends_with');
bors_transitional::function_include('html/bors_entity_decode');

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

	return join(', ', array_filter(array_merge($s1, $s2)));
}
