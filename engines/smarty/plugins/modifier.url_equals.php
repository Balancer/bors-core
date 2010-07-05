<?php

function smarty_modifier_url_equals($url1, $url2)
{
	$data1 = url_parse($url1);
	$data2 = url_parse($url2);

	return ($data1['uri'] == $data2['uri']);
}
