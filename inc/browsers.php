<?php

function get_browser_info($user_agent)
{
	$os = '';
	$ov = '';
	if(preg_match('!Linux!', $user_agent))
		$os = 'Linux';
	elseif(preg_match('!FreeBSD!', $user_agent))
		$os = 'FreeBSD';
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
		$os = 'WindowsXP';
	elseif(preg_match('!Windows NT 5.0!', $user_agent))
		$os = 'Windows2000';
	elseif(preg_match('!Windows 98!', $user_agent))
		$os = 'Windows98';
	elseif(preg_match('!Win98!', $user_agent))
		$os = 'Windows98';
	elseif(preg_match('!Windows!i', $user_agent))
		$os = 'Windows';

	$browser='';
	$ov = '';
	if(preg_match('!KHTML, like Gecko.*Chrome/(\S+)!', $user_agent, $bv))
		$browser='Google Chrome';
	elseif(preg_match('!Opera!', $user_agent))
		$browser='Opera';
	elseif(preg_match('!Konqueror!', $user_agent))
		$browser='Konqueror';
	elseif(preg_match('!SeaMonkey!', $user_agent))
		$browser = 'SeaMonkey';
	elseif(preg_match('!Iceweasel!', $user_agent))
		$browser = 'Iceweasel';
	elseif(preg_match('!Firefox!', $user_agent))
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
		$browser = 'MSIE';

	if(preg_match('!Akregator!', $user_agent))
	{
		$browser = 'Akregator';
		$os = 'Linux';
	}

	if(preg_match('!Yahoo!', $user_agent))
	{
		$browser = 'YahooBot';
		$os = 'YahooBot';
	}

	if(preg_match('!Rambler!', $user_agent))
	{
		$browser = 'RamblerBot';
		$os = 'RamblerBot';
	}

	if(preg_match('!Googlebot!', $user_agent))
	{
		$browser = 'GoogleBot';
		$os = 'GoogleBot';
	}

	if(preg_match('!msnbot!', $user_agent))
	{
		$browser = 'MSNBot';
		$os = 'MSNBot';
	}

	if(preg_match('!WebAlta!', $user_agent))
	{
		$browser = 'WebAltaBot';
		$os = 'WebAltaBot';
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
	}

	if(preg_match('!Nigma!', $user_agent))
	{
		$browser = 'NigmaBot';
		$os = 'NigmaBot';
	}

	if(preg_match('!Yanga!', $user_agent))
	{
		$browser = 'YangaBot';
		$os = 'YangaBot';
	}

	if(preg_match('/'.preg_quote('Opera/9.64 (; U; ru) Presto/2.1.1!', '/').'/', $user_agent))
	{
		$browser = 'Opera';
		$os = 'Unknown';
	}
	
	if($ov)
		$ov = $ov[1];

	if($bv)
		$bv = $bv[1];

	return array($os, $browser, $ov, $bv);
}
