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
