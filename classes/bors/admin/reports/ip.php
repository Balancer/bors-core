<?php

require_once('inc/clients/geoip-place.php');

class bors_admin_reports_ip extends bors_admin_page
{
	function title() { return ec('Информация об IP ').$this->ip(); }
	function nav_name() { return $this->ip(); }
	function ip() { return urldecode($this->id()); }
	function parents() { return array('/_bors/admin/reports/load/'); }

	function body_data()
	{
		$ip = $this->ip();
		$ips = explode('<br/>', $ip);

		if(count($ips) > 1)
			$ip = $ips[0];

		list($country_code, $country_name, $city_name, $city_object) = geoip_info($ip);

		require_once "Net/Whois.php"; // http://pear.php.net/manual/en/package.networking.net-whois.query.php
		$server = "whois.ripe.net"; // whois.arin.net
		$query  = $ip;     // get information about
                               // this domain
		$whois = new Net_Whois;
		$whois = $whois->query($query, $server);

		$dbh = new driver_mysql(config('bors_local_db'));

		$requests = $dbh->select_array('bors_access_log', '*', array(
			'user_ip IN' => $ips,
			'order' => 'access_time',
		));

		return compact('country_code', 'country_name', 'city_name', 'city_object', 'ip', 'requests', 'whois');
	}
}
