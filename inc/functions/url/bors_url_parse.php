<?php

function bors_url_parse($url, $field=NULL, $var=NULL, $default=NULL)
{
	$data = parse_url($url);
	if($field)
		$data = $data[$field];

	if($var)
	{
		$parsed_data = array();
		parse_str($data, $parsed_data);
		$data = defval($parsed_data, $var, $default);
	}

	return $data;
}
