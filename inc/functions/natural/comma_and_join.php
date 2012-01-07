<?php

// Соединение однородных членов через запятые и союз «и»:
// array('яблоки', 'груши', 'апельсины') => 'яблоки, груши и апельсины'

function comma_and_join($words, $delimiter = ', ')
{
	bors_function_include('locale/ec');

	$last = array_pop($words);
	if(empty($words))
		return $last;

	return join($delimiter, $words).ec(' и ').$last;
}
