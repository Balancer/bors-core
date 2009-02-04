<?php

function clean_int_list($list)
{
	if(is_numeric($list))
		return intval($list);
		
	$result = array();
	foreach(explode(",", $list) as $i)
		$result[] = intval($i);
		
	return join(",", $result);
}

function parse_condensed_list($list)
{
        $numbers = array();

        foreach(split(",", $list) as $n)
        {
			if(!$n)
				continue;

            if(strpos($n, '-') === false)
                $numbers[] = intval($n);
            else
            {
                list($b,$e) = split('-', $n);
                for($j = $b; $j <= $e; $j++)
                    $numbers[] = intval($j);
            }
        }

        return $numbers;
}
