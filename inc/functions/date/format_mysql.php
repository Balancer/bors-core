<?php

function date_format_mysql($time, $quoted=true)
{
	$q = $quoted ? "'" : '';
	return $time ? $q.date('Y-m-d', $time).$q : NULL;
}
