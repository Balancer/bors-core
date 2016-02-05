<?php

function geoip_place($ip)
{
	list($country_code, $country_name, $city_name) = geoip_info($ip);

	if($country_code)
	{
		$res = "$country_name";
		if($city_name)
			$res .= ", $city_name";
	}
	else
		$res = "";

	return $res;
}

function geoip_flag($ip, $fun = false, $must_be=false, $time = NULL)
{
	list($country_code, $country_name, $city) = geoip_info($ip);

	if(!$country_code)
	{
		if(!$must_be)
			return '';

		return '<img src="http://s.wrk.ru/f/xx.gif" class="flag" title="??" alt="??"/>';
	}

	$alt = array($country_name);
	if($city)
	{
		$alt[] = $city;
		// С 21.03.2014 Крым — российский
		if(preg_match('/Sevastopol|Simferopol|Znamenka/', $city) && (!$time || $time > 1395345600))
		{
			$alt[0] = 'Russian Federation';
			$country_code = 'RU';
		}
	}

	$file = bors_lower($country_code).".gif";
//	if(!file_exists("/var/www/balancer.ru/htdocs/img/flags/$file"))
//		$file = "-.gif";

	if($fun)
		array_unshift($alt, "Earth");

	$alt = join(", ", $alt);

	$res = '<img src="http://s.wrk.ru/f/'.$file.'" class="flag" title="'.addslashes($alt).'" alt="'.$country_code.'"/>';
	return $res;
}

/**
	Возвращает массив информации о клиенте по его IP:
	@return array($country_code, $country_name, $city_name, $city_object)
*/

function geoip_info($ip)
{
	if(!$ip)
		return array('','','', NULL);

	if(class_exists('\\GeoIp2\\Database\\Reader') && file_exists(BORS_EXT.'/data/geolite2/GeoLite2-City.mmdb'))
	{
		$reader = new \GeoIp2\Database\Reader(BORS_EXT.'/data/geolite2/GeoLite2-City.mmdb');

		$record = $reader->city($ip);

		//print($record->country->isoCode . "\n"); // 'US', 'RU'
		$country_code = $record->country->isoCode;

		// print($record->country->name . "\n"); // 'United States', 'Russia'
		$country_name = empty($record->country->names['ru']) ? $record->country->name : $record->country->names['ru'];

		//var_dump($record->country->names); // '美国', 'ru' => 'Россия'

		//print($record->mostSpecificSubdivision->name . "\n"); // 'Minnesota', 'Moscow'
		//print($record->mostSpecificSubdivision->isoCode . "\n"); // 'MN', 'MOW'

		//print($record->city->name . "\n"); // 'Minneapolis', 'Moscow'
		$city_name = empty($record->city->names['ru']) ? $record->city->name : $record->city->names['ru'];
		//var_dump($record->city->names); // 'ru' => 'Москва'

		//print($record->postal->code . "\n"); // '55455', ''

		//print($record->location->latitude . "\n"); // 44.9733; 55,7522
		//print($record->location->longitude . "\n"); // -93.2323; 37,6156

		$city_object = NULL;

		return [$country_code, $country_name, $city_name, $city_object];
	}

	if(!function_exists('geoip_country_code_by_name'))
		require_once(BORS_3RD_PARTY."/geoip/geoipcity.inc");

//	$ch = new bors_cache();
//	if($ch->get("users-geoip-info", $ip))
//		0;//return $ch->last();

	$country_code = '';
	$city_object = NULL;
	if(file_exists(($gf = BORS_3RD_PARTY."/geoip/GeoIPCity.dat")))
	{
		$gi = geoip_open($gf, GEOIP_STANDARD);

		$record = geoip_record_by_addr($gi, $ip);
		$country_code = $record->country_code;
		$country_name = $record->country_name;
		$city_name = $record->city;
		geoip_close($gi);
		$city_object = $record;
	}

	if(!$country_code && file_exists(($gf = BORS_3RD_PARTY."/geoip/GeoLiteCity.dat")))
	{
		$gi = geoip_open($gf, GEOIP_STANDARD);

		$record = geoip_record_by_addr($gi, $ip);
		if($record)
		{
			$country_code = $record->country_code;
			$country_name = $record->country_name;
			$city_name = $record->city;
		}
		geoip_close($gi);
		$city_object = $record;
	}

	if(!$country_code && @file_exists(($gf = "/usr/share/GeoIP/GeoIP.dat")) && function_exists('geoip_open'))
	{
		$gi = geoip_open($gf, GEOIP_STANDARD);
		$country_code = @geoip_country_code_by_addr($gi, $ip);
		$country_name = @geoip_country_name_by_addr($gi, $ip);
		$city_name = "";
		geoip_close($gi);
	}

	if(!$country_code && file_exists(($gf = BORS_3RD_PARTY."/geoip/GeoIP.dat")) && function_exists('geoip_open'))
	{
		$gi = geoip_open($gf, GEOIP_STANDARD);
		$country_code = geoip_country_code_by_addr($gi, $ip);
		$country_name = geoip_country_name_by_addr($gi, $ip);
		$city_name = "";
		geoip_close($gi);
	}

	$country_code  = iconv('ISO-8859-1', 'utf-8', $country_code );
	$country_name  = iconv('ISO-8859-1', 'utf-8', @$country_name );
	$city_name = iconv('ISO-8859-1', 'utf-8', @$city_name);

	if(!function_exists('geoip_open') && function_exists('geoip_country_code_by_name'))
	{
		if(!$country_code)
			$country_code = geoip_country_code_by_name($ip);

		if(!$country_name)
			$country_name = geoip_country_name_by_name($ip);

		if(!$city_name)
			$city_name = @geoip_org_by_name($ip); // Нужен /usr/share/GeoIP/GeoIPOrg.dat

		if(!$city_name)
			$city_name = @geoip_isp_by_name($ip); // Нужен /usr/share/GeoIP/GeoIPISP.dat
	}

	return array($country_code, $country_name, $city_name, $city_object);
//	return $ch->set(array($country_code, $country_name, $city_name, $city_object), -3600);
}
