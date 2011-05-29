<?php

require_once('inc/clients/geoip-place.php');

class bors_admin_reports_ip extends bors_admin_page
{
	function title() { return ec('Информация об IP ').$this->ip(); }
	function nav_name() { return $this->ip(); }
	function ip() { return $this->id(); }
	function parents() { return array('/_bors/admin/reports'); }

	function body_data()
	{
		$ip = $this->ip();
		list($country_code, $country_name, $city_name, $city_object) = geoip_info($ip);

		require_once "Net/Whois.php"; // http://pear.php.net/manual/en/package.networking.net-whois.query.php
		$server = "whois.ripe.net"; // whois.arin.net
		$query  = $ip;     // get information about
                               // this domain
		$whois = new Net_Whois;
		$whois = $whois->query($query, $server);

		return compact('country_code', 'country_name', 'city_name', 'city_object', 'ip', 'whois');
	}
}
