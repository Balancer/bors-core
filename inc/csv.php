<?php

// Explode CSV string
function csv_explode($str, $delim = ',', $qual = "\"")
{
	$skipchars = array( $qual, "\\" );
	$len = bors_strlen($str);
	$inside = false;
	$word = '';

	for ($i = 0; $i < $len; ++$i) 
	{
		$c=bors_substr($str,$i,1);
		if($c == $delim && !$inside)
		{
			$out[] = $word;
			$word = '';
		}
		elseif($inside && in_array($c, $skipchars) && ($i<$len && bors_substr($str,$i+1,1) == $qual))
		{
			$word .= $qual;
			$i++;
		}
		elseif($c == $qual)
			$inside = !$inside;
		else
			$word .= $c;
	}

	$out[] = $word;

	return $out;
}
