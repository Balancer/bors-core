<?php

function bors_entity_decode($string)
{
	static $ics = NULL;
	if(is_null($ics))
		$ics = config('internal_charset');
	return html_entity_decode($string, ENT_COMPAT, $ics);
}
