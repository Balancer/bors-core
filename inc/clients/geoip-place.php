<?php

function geoip_place($ip)
{
	list($cc, $cn, $city) = geoip_info($ip);

	if($cc)
	{
		$res = "$cn";
		if($city)
			$res .= ", $city";
	}
	else
		$res = "";

	return $res;
}

function geoip_flag($ip, $fun = false)
{
	list($cc, $cn, $city) = geoip_info($ip);

	if(!$cc)
		return '';

	$alt = "$cn";
	if($city)
		$alt .= ", $city";

	$file = bors_lower($cc).".gif";
//	if(!file_exists("/var/www/balancer.ru/htdocs/img/flags/$file"))
//		$file = "-.gif";

	if($fun)
		$alt = "Earth, {$alt}";

	$res = '<img src="http://s.wrk.ru/f/'.$file.'" class="flag" title="'.addslashes($alt).'" alt="'.$cc.'"/>';
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

	require_once(BORS_3RD_PARTY."/geoip/geoip.inc");
	require_once(BORS_3RD_PARTY."/geoip/geoipcity.inc");

//	$ch = new Cache();
//	if($ch->get("users-geoip-info", $ip))
//		0;//return $ch->last();

	$cc = '';
	$city_object = NULL;
	if(file_exists(($gf = BORS_3RD_PARTY."/geoip/GeoIPCity.dat")))
	{
		$gi = geoip_open($gf, GEOIP_STANDARD);

		$record = geoip_record_by_addr($gi, $ip);
		$cc = $record->country_code;
		$cn = $record->country_name;
		$cin = $record->city;
		geoip_close($gi);
		$city_object = $record;
	}

	if(!$cc && file_exists(($gf = BORS_3RD_PARTY."/geoip/GeoLiteCity.dat")))
	{
		$gi = geoip_open($gf, GEOIP_STANDARD);

		$record = geoip_record_by_addr($gi, $ip);
		if($record)
		{
			$cc = $record->country_code;
			$cn = $record->country_name;
			$cin = $record->city;
		}
		geoip_close($gi);
		$city_object = $record;
	}

	if(!$cc && @file_exists(($gf = "/usr/share/GeoIP/GeoIP.dat")))
	{
		$gi = geoip_open($gf, GEOIP_STANDARD);
		$cc = geoip_country_code_by_addr($gi, $ip);
		$cn = geoip_country_name_by_addr($gi, $ip);
		$cin = "";
		geoip_close($gi);
	}

	if(!$cc && file_exists(($gf = BORS_3RD_PARTY."/geoip/GeoIP.dat")))
	{
		$gi = geoip_open($gf, GEOIP_STANDARD);
		$cc = geoip_country_code_by_addr($gi, $ip);
		$cn = geoip_country_name_by_addr($gi, $ip);
		$cin = "";
		geoip_close($gi);
	}

	$cc  = iconv('ISO-8859-1', 'utf-8', $cc );
	$cn  = iconv('ISO-8859-1', 'utf-8', $cn );
	$cin = iconv('ISO-8859-1', 'utf-8', $cin);

	return array($cc, $cn, $cin, $city_object);
//	return $ch->set(array($cc, $cn, $cin, $city_object), -3600);
}
