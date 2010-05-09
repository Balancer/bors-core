<?php

function get_browser_info($user_agent)
{
	$os = '';
	$ov = '';

	$is_bot = false;

	if(preg_match('!FreeBSD!', $user_agent))
		$os = 'FreeBSD';
	elseif(preg_match('!Gentoo!', $user_agent))
	{
		$os = 'Linux';
		$ov = 'Gentoo';
	}
	elseif(preg_match('!Linux!', $user_agent))
		$os = 'Linux';
	elseif(preg_match('!Windows CE; PPC!', $user_agent))
		$os = 'PocketPC';
	elseif(preg_match('!iPhone!', $user_agent))
		$os = 'iPhone';
	elseif(preg_match('!Symbian OS!', $user_agent))
		$os = 'Symbian';
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
	elseif(preg_match('!Windows NT 6.0!', $user_agent))
		$os = 'WindowsVista';
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

	$browser='';
	$bv = '';
	if(preg_match('!KHTML, like Gecko.*Chrome/(\S+)!', $user_agent, $m))
	{
		$browser='Google Chrome';
		$bv = $m[1];
	}
	elseif(preg_match('!Opera!', $user_agent))
		$browser='Opera';
	elseif(preg_match('!Konqueror!', $user_agent))
		$browser='Konqueror';
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

	if(preg_match('!Akregator!', $user_agent))
	{
		$browser = 'Akregator';
		$os = 'Linux';
	}

	if(preg_match('!Yahoo!', $user_agent))
	{
		$browser = 'YahooBot';
		$os = 'YahooBot';
		$is_bot = true;
	}

	if(preg_match('!Rambler!', $user_agent))
	{
		$browser = 'RamblerBot';
		$os = 'RamblerBot';
		$is_bot = true;
	}

	if(preg_match('!Googlebot!', $user_agent))
	{
		$browser = 'GoogleBot';
		$os = 'GoogleBot';
		$is_bot = true;
	}

	if(preg_match('!msnbot!', $user_agent))
	{
		$browser = 'MSNBot';
		$os = 'MSNBot';
		$is_bot = true;
	}

	if(preg_match('!WebAlta!', $user_agent))
	{
		$browser = 'WebAltaBot';
		$os = 'WebAltaBot';
		$is_bot = true;
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

	if(preg_match('!Yandex!', $user_agent))
	{
		$browser = 'YandexBot';
		$os = 'YandexBot';
		$is_bot = true;
	}

	if(preg_match('!Nigma!', $user_agent))
	{
		$browser = 'NigmaBot';
		$os = 'NigmaBot';
		$is_bot = true;
	}

	if(preg_match('!Yanga!', $user_agent))
	{
		$browser = 'YangaBot';
		$os = 'YangaBot';
		$is_bot = true;
	}

	if(preg_match('!Speedy Spider!', $user_agent))
	{
		$browser = 'EntirewebBot';
		$os = '';
		$is_bot = true;
	}

	if(preg_match('!Opera/\d+\.\d+ \(; U; \w+\) Presto/[\d\.]+!', $user_agent))
	{
		$browser = 'Opera';
		$os = 'Unknown';
	}

	if(!$is_bot && preg_match('/(bot|crowler|spider)/i', $user_agent))
	{
		debug_hidden_log('__need-append-data', 'Unknown bot: '.$user_agent);
		$is_bot = true;
	}

	return array($os, $browser, $ov, $bv, $is_bot);
}

function bors_find_shared_file($base_name, $path, $default = 'unknown.png')
{
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

function bors_browser_images($ua)
{
	list($os, $browser, $osver, $bver, $is_bot) = get_browser_info($ua);

	$short = array();
	if($os) $short[] = "os=$os";
	if($osver) $short[] = "ov=$osver";
	if($browser) $short[] = "b=$browser";
	if($bver) $short[] = "bv=$bver";
	if($is_bot) $short[] = "bot";

	$title = htmlspecialchars($ua." [".join(',', $short)."]");

	$info = array();

	if(!$is_bot)
	{
		if(($ofile = bors_find_shared_file(bors_lower("$os-$osver"), 'images/os', false)))
			$info[] = "<img src=\"/_bors/$ofile\" class=\"flag\" title=\"$title\" alt=\"$bver\"/>";
		elseif(($ofile = bors_find_shared_file(bors_lower($os), 'images/os', false)))
			$info[] = "<img src=\"/_bors/$ofile\" class=\"flag\" title=\"$title\" alt=\"$bver\"/>";
	}

	if(($bfile = bors_find_shared_file(bors_lower("$browser-$bver"), 'images/browsers', false)))
		$info[] = "<img src=\"/_bors/$bfile\" class=\"flag\" title=\"$title\" alt=\"$over\"/>";
	elseif(($bfile = bors_find_shared_file(bors_lower($browser), 'images/browsers', false)))
		$info[] = "<img src=\"/_bors/$bfile\" class=\"flag\" title=\"$title\" alt=\"$over\"/>";
	elseif($is_bot && ($bfile = bors_find_shared_file('spider-unknown', 'images/browsers', false)))
		$info[] = "<img src=\"/_bors/$bfile\" class=\"flag\" title=\"$title\" alt=\"$over\"/>";

	if(empty($info))
		$info[] = "<img src=\"/_bors/i/unknown-16.png\" class=\"flag\" title=\"$title\" alt=\"Unknown\"/>";

	return join('', $info);
}
