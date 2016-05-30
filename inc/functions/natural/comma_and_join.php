<?php

// Соединение однородных членов через запятые и союз «и»:
// array('яблоки', 'груши', 'апельсины') => 'яблоки, груши и апельсины'

function comma_and_join($words, $delimiter = ', ')
{
	require_once __DIR__.'/../locale/ec.php';

	$last = array_pop($words);
	if(empty($words))
		return $last;

	return join($delimiter, $words).ec(' и ').$last;
}
