<?php

function url_section_match($main_url, $section_url)
{
	if(preg_match('/^\w+$/', $section_url))
		$section_url = "/{$section_url}/";

	bors_use('url/bors_url_parse');
	bors_use('string/bors_starts_with');

	$udm = bors_url_parse($main_url);
	$uds = bors_url_parse($section_url);

	return bors_starts_with($udm['path'], $uds['path']);
}
