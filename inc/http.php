<?php

function http_get($url)
{
	$ch = curl_init($url);
	curl_setopt_array($ch, array(
		CURLOPT_TIMEOUT => 5,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_MAXREDIRS => 3,
		CURLOPT_ENCODING => 'gzip, deflate',
//		CURLOPT_RANGE => '0-4095',
//		CURLOPT_REFERER => $original_url,
		CURLOPT_AUTOREFERER => true,
//		CURLOPT_HTTPHEADER => $header,
		CURLOPT_USERAGENT => 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; FunWebProducts; .NET CLR 1.1.4322; .NET CLR 2.0.50727)',
		CURLOPT_RETURNTRANSFER => true,
	));
	
	if(preg_match("!lenta\.ru!", $url))
		curl_setopt($ch, CURLOPT_PROXY, 'home.balancer.ru:3128');
		
	$data = curl_exec($ch);
	
//	print_r($data);
	
	curl_close($ch);
	
	return $data;
}
