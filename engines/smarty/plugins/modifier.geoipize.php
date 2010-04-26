<?php

function smarty_modifier_geoipize($ip)
{
	require_once('inc/clients/geoip-place.php');
	$place = geoip_place($ip);
	if($place)
		$ip .= " ($place)";

	return $ip;
}
