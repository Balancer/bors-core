<?php

function get_browser_info($user_agent, $log_unknown = true)
{
	$os = '';
	$ov = '';
	$device = NULL;

	if($bot = bors_bot_detect($user_agent))
	{
		$browser = $bot;
		$is_bot = true;
	}
	else
		$is_bot = false;

	// ************************************************************
	// Обнаруживаем устройства
	// ************************************************************

	if(preg_match('!Desire.*HD!i', $user_agent))
	{
		$device = 'HTC Desire HD';
	}
	elseif(preg_match('!NokiaN70!i', $user_agent))
	{
		$device = 'Nokia N70';
	}
	elseif(preg_match('!NokiaC6-00!i', $user_agent))
	{
		$device = 'Nokia';
		$os = 'Symbian';
		$ov = 'Series 60';
	}
	elseif(preg_match('!Nokia!i', $user_agent))
	{
		$device = 'Nokia';
	}
	elseif(preg_match('!iPad!', $user_agent))
	{
		$os = 'iOS';
		if(preg_match('/ OS (\d+)_/', $user_agent, $m))
			$ov = $m[1];
		$device = 'iPad';
	}
	elseif(preg_match('!iPhone!', $user_agent))
	{
		$os = 'iOS';
		if(preg_match('/ OS (\d+)_/', $user_agent, $m))
			$ov = $m[1];
		$device = 'iPhone';
	}
	elseif(preg_match('!kindle!i', $user_agent))
	{
		$os = 'Linux';
		$device = 'Amazon Kindle';
	}
	elseif(preg_match('!P3400!i', $user_agent))
	{
		$os = 'Windows Mobile';
		$ov = '2005';
		$device = 'P3400 Gene';
		$os_img = 'pocketpc';
		$device_img = 'communicator-old';
//		$device_img = 'htc';
	}

	// ************************************************************
	// Обнаруживаем ОС и устройства
	// ************************************************************

	if(!$os)
	{
		if(preg_match('!Android!i', $user_agent))
		{
			$os = 'Android';
			if(preg_match('!Android ([\d\.]+);!', $user_agent, $m))
				$osv = $m[1];
		}
		elseif(preg_match('!FreeBSD!', $user_agent))
			$os = 'FreeBSD';
		elseif(preg_match('!Mint!', $user_agent))
		{
			$os = 'Linux';
			$ov = 'Mint';
		}
		elseif(preg_match('!Ubuntu!', $user_agent))
		{
			$os = 'Linux';
			$ov = 'Ubuntu';
		}
		elseif(preg_match('!Gentoo!', $user_agent))
		{
			$os = 'Linux';
			$ov = 'Gentoo';
		}
		elseif(preg_match('!OpenBSD!', $user_agent))
		{
			$os = 'OpenBSD';
		}
		elseif(preg_match('!Linux!', $user_agent))
			$os = 'Linux';
		elseif(preg_match('!Windows CE; PPC!', $user_agent))
			$os = 'PocketPC';
		elseif(preg_match('!Series\s*(\d+)!', $user_agent, $m))
		{
			$os = 'Symbian';
			$ov = 'Series '.$m[1];
		}
		elseif(preg_match('!(Symbian OS|SymbOS)!', $user_agent))
		{
			$os = 'Symbian';
			if(preg_match('!S(\d+);!', $user_agent, $m))
				$ov = 'Series '.$m[1];
		}
		elseif(preg_match('!J2ME!', $user_agent))
			$os = 'J2ME';
		elseif(preg_match('!Intel Mac OS X!', $user_agent))
			$os = 'MacOSX';
		elseif(preg_match('!Macintosh; PPC Mac OS X!', $user_agent))
			$os = 'MacOSX';
		elseif(preg_match('!OS/2;!', $user_agent))
			$os = 'OS/2';
		elseif(preg_match('!J2ME!', $user_agent))
			$os = 'J2ME';
		elseif(preg_match('!Tablet PC 1.7!', $user_agent))
		{
			$os = 'Windows';
			$ov = 'XP Tablet PC Edition 2005';
		}
		elseif(preg_match('!Windows NT 6\.2!', $user_agent))
		{
			$os = 'Windows';
			$ov = '8';
		}
		elseif(preg_match('!Windows NT 6\.1!', $user_agent))
		{
			$os = 'Windows';
			$ov = '7';
		}
		elseif(preg_match('!Windows NT 6.0!', $user_agent))
		{
			$os = 'Windows';
			$ov = 'Vista';
		}
		elseif(preg_match('!Windows NT 5.(1|2)!', $user_agent))
		{
			$os = 'Windows';
			$ov = 'XP';
		}
		elseif(preg_match('!Windows NT 5.0!', $user_agent))
		{
			$os = 'Windows';
			$ov = '2000';
		}
		elseif(preg_match('!Windows 98!', $user_agent))
			$os = 'Windows98';
		elseif(preg_match('!Win98!', $user_agent))
			$os = 'Windows98';
		elseif(preg_match('!Windows!i', $user_agent))
			$os = 'Windows';
		elseif(preg_match('!RIM Tablet OS!', $user_agent))
			$os = 'BlackBerry';
	}

	// ************************************************************
	// Обнаруживаем браузеры
	// ************************************************************
	$browser='';
	$browser_name='';
	$bv = '';
	if(preg_match('!YaBrowser/(\d+)!', $user_agent, $m))
	{
		$browser='Яндекс.Браузер';
		$browser_name='YaBrowser';
		$bv = $m[1];
	}
	elseif(preg_match('!UC\s*Browser!', $user_agent))
		$browser = 'UC Browser';
	elseif(preg_match('!Chromium/(\d+)!', $user_agent, $m))
	{
		$browser='Chromium';
		$bv = $m[1];
	}
	elseif(preg_match('!KHTML, like Gecko.*Chrome/(\S+)!', $user_agent, $m))
	{
		$browser='Google Chrome';
		$bv = $m[1];
	}
	elseif(preg_match('!Opera!', $user_agent))
		$browser='Opera';
	elseif(preg_match('!Konqueror!', $user_agent))
		$browser='Konqueror';
	elseif(preg_match('!w3m!', $user_agent))
	{
		$browser = 'w3m';
		$os = 'Linux';
	}
	elseif(preg_match('!SeaMonkey!', $user_agent))
		$browser = 'SeaMonkey';
	elseif(preg_match('!Iceweasel!', $user_agent))
		$browser = 'Iceweasel';
	elseif(preg_match('!Firefox!', $user_agent))
	{
		$browser = 'Firefox';
		if(preg_match('!Firefox/([\d\.]+)!', $user_agent, $m))
			$bv = $m[1];
	}
	elseif(preg_match('! Firef !', $user_agent))
		$browser = 'Firefox';
	elseif(preg_match('!Shiretoko!', $user_agent))
		$browser = 'Firefox';
	elseif(preg_match('!GranParadiso!', $user_agent))
		$browser = 'Firefox';
	elseif(preg_match('!Safari!', $user_agent))
		$browser = 'Safari';
	elseif(preg_match('!Gecko!', $user_agent))
		$browser = 'Gecko';
	elseif(preg_match('!MSIE!', $user_agent))
	{
		if(preg_match('!MSIE ([\d\.]+);!', $user_agent, $m))
		{
			$browser = 'IE'.intval($m[1]);
			$bv = $m[1];
		}
		else
		{
			$browser = 'MSIE';
			$bv = '';
		}
	}
	elseif(preg_match('!MIDP!', $user_agent))
		$browser = 'MIDP';

	if(preg_match('!Akregator!', $user_agent))
	{
		$browser = 'Akregator';
		$os = 'Linux';
	}

	if(preg_match('!Anonymouse.org!', $user_agent))
	{
		$browser = 'Anonymouse.org';
		$os = 'Anonymouse.org';
	}

	if(preg_match('!libwww-perl!', $user_agent))
	{
		$browser = 'libwww-perl';
		$os = 'libwww-perl';
	}

	if(preg_match('!Download Master!', $user_agent))
	{
		$browser = 'Download Master';
		$os = 'Windows';
	}

	if(preg_match('!Opera/\d+\.\d+ \(; U; \w+\) Presto/[\d\.]+!', $user_agent))
	{
		$browser = 'Opera';
		$os = 'Unknown';
	}

	if(!$is_bot && (!$browser or !$os) && $log_unknown)
		debug_hidden_log('user_agents', "Unknown user agent '{$user_agent}'");

	if(!$browser_name)
		$browser_name = $browser;

	return array($os, $browser, $ov, $bv, $is_bot, $device, @$os_img, @$browser_img, @$device_img, $browser_name);
}

function bors_find_shared_file($base_name, $path, $default = 'unknown.png')
{
	$base_name = bors_lower($base_name);
	$base_name = preg_replace('/\W/', '-', $base_name);
//if(preg_match('/series/', $base_name))
//	exit($base_name);
	$dir = BORS_CORE;
	if(file_exists("$dir/shared/".($file = "$path/$base_name.png")))
		return $file;
	if(file_exists("$dir/shared/".($file = "$path/$base_name.gif")))
		return $file;
	if(file_exists("$dir/shared/".($file = "$path/$base_name.jpg")))
		return $file;

	if($default && file_exists("$dir/shared/".($file = "$path/$default")))
		return $file;

	return NULL;
}

function bors_browser_images($ua, $ip = NULL)
{
	list($client_name, $os) = im_client_detect($ua.'-'.$ip, NULL); // xmpp для блогов, по типу
	if($client_name)
	{
		$client_image = im_client_image($client_name);
		return '<span title="'.htmlspecialchars($client_name)."\"><img src=\"{$client_image}\" width=\"16\" height=\"16\"></span>";
	}

	list($os, $browser, $osver, $bver, $is_bot, $device, $os_img, $browser_img, $device_img, $browser_name) = get_browser_info($ua);

	$short = array();
	if($browser || $bver)
		$short[] = trim("$browser $bver");
	if($os || $osver)
		$short[] = trim("$os $osver");
	if($device)
		$short[] = $device;

	if($is_bot) $short[] = "bot";

	$title = htmlspecialchars(join(', ', array_unique($short))." [{$ua}]");

	$info = array();

	if(($bfile = bors_find_shared_file("$browser_name-$bver", 'images/browsers', false)))
		$info[] = "<img src=\"/_bors/$bfile\" class=\"i16\" alt=\"$bver\"/>";
	elseif(($bfile = bors_find_shared_file($browser_name, 'images/browsers', false)))
		$info[] = "<img src=\"/_bors/$bfile\" class=\"i16\" alt=\"$bver\"/>";
	elseif($is_bot && ($bfile = bors_find_shared_file('spider-unknown', 'images/browsers', false)))
		$info[] = "<img src=\"/_bors/$bfile\" class=\"i16\" alt=\"$bver\"/>";

	if(!$is_bot)
	{
		if(!empty($os_img) && ($bfile = bors_find_shared_file($os_img, 'images/os', false)))
			$info[] = "<img src=\"/_bors/$bfile\" class=\"i16\" alt=\"$over\"/>";
		elseif(($ofile = bors_find_shared_file("$os-$osver", 'images/os', false)))
			$info[] = "<img src=\"/_bors/$ofile\" class=\"i16\" alt=\"$bver\"/>";
		elseif(($ofile = bors_find_shared_file($os, 'images/os', false)))
			$info[] = "<img src=\"/_bors/$ofile\" class=\"i16\" alt=\"$bver\"/>";
	}

	if(!empty($device_img) && ($device_image = bors_find_shared_file($device_img, 'i16/dev', false)))
		$info[] = "<img src=\"/_bors/$device_image\" class=\"i16\" alt=\"$over\"/>";
	elseif(($device_image = bors_find_shared_file("$device", 'i16/dev', false)))
		$info[] = "<img src=\"/_bors/$device_image\" class=\"i16\" alt=\"$over\"/>";

	if(empty($info))
	{
		$info[] = "<img src=\"/_bors/i/unknown-16.png\" class=\"i16\" alt=\"Unknown\"/>";
		debug_hidden_log('append_data', "Unknown user agent $ua - $ip [browser=$browser; bver=$bver; os=$os; osver=$osver]");
	}

	return "<span title=\"$title\">".join('', $info)."</span>";
}
