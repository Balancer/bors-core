<?php

bors_function_include('client/bors_bot_detect');
bors_function_include('client/bors_client_analyze');

function bors_client_info_short($ip, $ua = '')
{
	$info = array();

	include_once('inc/clients/geoip-place.php');
	include_once('inc/browsers.php');
	if(function_exists('geoip_flag'))
		$info[] = geoip_flag($ip);

	return join('', $info).bors_browser_images($ua);
}

function im_client_detect($client_id, $type)
{
	if(preg_match('/purple/i', $client_id))
		return array('Pidgin', NULL);

	if(preg_match('/gajim/i', $client_id))
		return array('Gajim', NULL);

	if(preg_match('/qip/i', $client_id))
		return array('QIP', 'Windows');

	if(preg_match('!gmail\.com/gmail!i', $client_id))
		return array('GTalk/GMail', NULL);

	switch($type)
	{
		case 'jabber':
		case 'xmpp':
			return array('Jabber', NULL);
	}

	return array(NULL, NULL);
}

function im_client_image($client_name)
{
	if(!$client_name)
		return NULL;

	switch($client_name)
	{
		case 'Pidgin':
			return 'http://s.wrk.ru/i16/im/pidgin.png';
		case 'Jabber':
			return 'http://s.wrk.ru/i16/im/jabber.jpg';
		case 'Gajim':
			return 'http://s.wrk.ru/i16/im/gajim.png';
		case 'QIP':
			return 'http://s.wrk.ru/i16/im/qipinfium.png';
		case 'GTalk/GMail':
			return 'http://s.wrk.ru/i16/im/gtalk.gif';
	}

	debug_hidden_log('append_data', "Unknown IM type $name for $client_id (of $type)");
	return NULL;
}

function os_image($os_name)
{
	switch($os_name)
	{
		case 'Linux':
			return '/bors-shared/images/os/linux.gif';
		case 'FreeBSD':
			return '/bors-shared/images/os/freebsd.png';
		case 'MacOSX':
			return '/bors-shared/images/os/macos.gif';
		case 'iPhone':
			return '/bors-shared/images/os/iphone.gif';
		case 'Symbian':
			return '/bors-shared/images/os/symbian.gif';
		case 'J2ME':
			return '/bors-shared/images/os/java.gif';
		case 'OS/2':
			return '/bors-shared/images/os/os2.gif';
		case 'PocketPC':
		case 'J2ME':
				break;
		case 'WindowsVista':
		case 'WindowsXP':
		case 'Windows2000':
		case 'Windows98':
		case 'Windows98':
		case 'Windows':
			return '/bors-shared/images/os/windows.gif';
			break;
		case 'Windows8':
			return '/bors-shared/images/os/windows-8.png';
			break;
		default:
	}

	return NULL;
}
