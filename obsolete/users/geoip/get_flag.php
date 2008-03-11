<?
	include_once("{$_SERVER['DOCUMENT_ROOT']}/cms/config.php");

	function get_flag($ip)
	{
		include_once("3part/geoip/geoip.inc");
		include_once("3part/geoip/geoipcity.inc");
		
		$ch = &new Cache();
		if($ch->get("country_flag-v7", $ip))
			return $ch->last();

		$gi = geoip_open("/var/www/balancer.ru/htdocs/cms/3part/geoip/GeoIPCity.dat", GEOIP_STANDARD);

		$record = geoip_record_by_addr($gi, $ip);
		$cc = $record->country_code;
		$cn = $record->country_name;
		$cin = $record->city;
		geoip_close($gi);
		
		if(!$cc)
		{
			$gi = geoip_open("/usr/share/GeoIP/GeoIP.dat", GEOIP_STANDARD);
			$cc = geoip_country_code_by_addr($gi, $ip);
			$cn = geoip_country_name_by_addr($gi, $ip);
			$cin = "";
			geoip_close($gi);
		}

		if($cc)
		{
			$alt = "$cn";
			if($cin)
				$alt .= ", $cin";

			$file = strtolower($cc).".gif";
			if(!file_exists("/var/www/balancer.ru/htdocs/img/flags/$file"))
				$file = "-.gif";
			$res = '<img src="http://balancer.ru/img/flags/'.$file.'" width="20" height="12" border="0" align="absmiddle" title="'.addslashes($alt).'" alt="'.$cc.'"/>';
		}
		else
			$res = "";

		return $ch->set($res, -3600);
	}

	function get_my_flag()
	{
		$outher = get_flag($_SERVER['REMOTE_ADDR']);
		if(!$_SERVER['HTTP_X_FORWARDED_FOR'])
			return $outher;
			
		$inner = get_flag($_SERVER['HTTP_X_FORWARDED_FOR']);
		if($inner && $inner != $outher)
			return "$outher/$inner";
		else
			return $outher;
	}
