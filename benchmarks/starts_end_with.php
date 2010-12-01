<?php

/**
	Тест измерения производительности разных вариантов функций с
	http://stackoverflow.com/questions/834303/php-startswith-and-endswith-functions

	Результаты:
		php-5.3.3, Q6600, 32 бита:
			startsWith_pos ... 0.4492199420929; ok
			startsWith_sub ... 0.67195701599121; ok
			startsWith_cmprev ... 0.68104982376099; ok
			startsWith_re ... 0.73672199249268; ok
			startsWith_cmp1 ... 0.91414713859558; ok


			endsWith_sub ... 0.71000194549561; ok
			endsWith_re ... 0.7322678565979; ok
			endsWith_pos ... 0.92304110527039; ok
			endsWith_cmp1 ... 1.1239938735962; ok
			endsWith_cmprev ... 1.3345930576324; ok
*/

function startsWith_re($haystack, $needle)
{
    return preg_match('/^'.preg_quote($needle)."/", $haystack);
}

function endsWith_re($haystack, $needle)
{
    return preg_match("/".preg_quote($needle) .'$/', $haystack);
}

function startsWith_cmp1($haystack, $needle, $case=true)
{
    if($case)
    	return strcmp(substr($haystack, 0, strlen($needle)), $needle) === 0;

    return strcasecmp(substr($haystack, 0, strlen($needle)), $needle) === 0;
}

function endsWith_cmp1($haystack, $needle, $case=true)
{
    if($case)
    	return strcmp(substr($haystack, strlen($haystack) - strlen($needle)), $needle) === 0;

    return strcasecmp(substr($haystack, strlen($haystack) - strlen($needle)), $needle) === 0;
}

function startsWith_sub($string, $char)
{
    $length = strlen($char);
    return (substr($string, 0, $length) === $char);
}

function endsWith_sub($string, $char)
{
    $length = strlen($char);
    return (substr($string, -$length, $length) === $char);
}

function startsWith_pos($haystack, $needle, $case=true)
{
   if($case)
       return strpos($haystack, $needle, 0) === 0;

   return stripos($haystack, $needle, 0) === 0;
}

function endsWith_pos($haystack, $needle, $case=true)
{
  $expectedPosition = strlen($haystack) - strlen($needle);

  if($case)
      return strrpos($haystack, $needle, 0) === $expectedPosition;

  return strripos($haystack, $needle, 0) === $expectedPosition;
}

function startsWith_cmprev($haystack, $needle, $case=true)
{
    if ($case)
        return strncmp($haystack, $needle, strlen($needle)) == 0;
    else
        return strncasecmp($haystack, $needle, strlen($needle)) == 0;
}

function endsWith_cmprev($haystack, $needle, $case=true)
{
     return startsWith_cmprev(strrev($haystack),strrev($needle),$case);
}

foreach(explode(' ', 'starts ends') as $type)
{
	foreach(explode(' ', 're cmp1 sub pos cmprev') as $f)
	{
		$fn = "{$type}With_{$f}";
		$test1 = 'owner_id';
		$test2 = 'create_date';
		$sum1 = 0;
		$sum2 = 0;
		$cycles = 10000;
		$start = microtime(true);
		echo "$fn ... ";
		for($i=0; $i<$cycles; $i++)
		{
			if($fn($test1, '_id'))
				$sum1++;

			if($fn($test2, 'create_'))
				$sum2++;
		}
		$time = microtime(true) - $start;
		echo $time . '; ' . ($sum1 + $sum2 == $cycles ? "ok\n" : "fail [$sum1,$sum2]\n");
	}
	echo "\n";
}
