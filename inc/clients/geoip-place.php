<?php

function geoip_place($ip)
{
	if(!$ip)
		return "";

	require_once(BORS_3RD_PARTY."/geoip/geoip.inc");
	require_once(BORS_3RD_PARTY."/geoip/geoipcity.inc");

	$ch = new Cache();
	if($ch->get("users-geoip-place", $ip))
		return $ch->last();

	$cc = '';
	if(file_exists(($gf = BORS_3RD_PARTY."/geoip/GeoIPCity.dat")))
	{
		$gi = geoip_open($gf, GEOIP_STANDARD);

		$record = geoip_record_by_addr($gi, $ip);
		$cc = $record->country_code;
		$cn = $record->country_name;
		$cin = $record->city;
		geoip_close($gi);
	}

	if(!$cc && file_exists(($gf = BORS_3RD_PARTY."/geoip/GeoLiteCity.dat")))
	{
		$gi = geoip_open($gf, GEOIP_STANDARD);

		$record = geoip_record_by_addr($gi, $ip);
		$cc = $record->country_code;
		$cn = $record->country_name;
		$cin = $record->city;
		geoip_close($gi);
	}

	if(!$cc && file_exists(($gf = "/usr/share/GeoIP/GeoIP.dat")))
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

	if($cc)
	{
		$res = "$cn";
		if($cin)
			$res .= ", $cin";
	}
	else
		$res = "";

	return $ch->set($res, -3600);
}
