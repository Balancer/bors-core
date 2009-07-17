<?php

function bors_bot_detect($user_agent)
{
	foreach(array(
			'Nigma' => 'Nigma',
			'yahoo' => 'Yahoo',
			'rambler' => 'Rambler',
			'google' => 'Googlebot',	// Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)
			'yandex' => 'Yandex',
			'Yanga' => 'Yanga',
			'Begun Robot Crawler' => 'Begun Robot Crawler',
			'msnbot' => 'MSN', 			// msnbot/2.0b (+http://search.msn.com/msnbot.htm)
			'OOZBOT' => 'OOZBOT', 		// OOZBOT/0.20 ( http://www.setooz.com/oozbot.html ; agentname at setooz dot_com )
			'Tagoobot' => 'Tagoobot',	// Mozilla/5.0 (compatible; Tagoobot/3.0; +http://www.tagoo.ru)
			'princeton crawler' => 'princeton crawler',	// nu_tch-princeton/Nu_tch-1.0-dev (princeton crawler for cass project; http://www.cs.princeton.edu/cass/; zhewang a_t cs ddot princeton dot edu)
			'DotBot' => 'DotBot',		// Mozilla/5.0 (compatible; DotBot/1.1; http://www.dotnetdotcom.org/, crawler@dotnetdotcom.org)
			'VoilaBot' => 'VoilaBot',	// Mozilla/5.0 (Windows; U; Windows NT 5.1; fr; rv:1.8.1) VoilaBot BETA 1.2 (support.voilabot@orange-ftgroup.com)
			'ovalebot' => 'ovalebot',	// ovalebot3.ovale.ru facepage
			'Nutch'	=> 'Nutch',			// gh-index-bot/Nutch-1.0 (GH Web Search.; lucene.apache.org; gh_email at someplace dot com)
			'Gigabot' => 'Gigabot',		// Gigabot/3.0 (http://www.gigablast.com/spider.html)
			'Exabot' => 'Exabot',		// Mozilla/5.0 (compatible; Exabot-Images/3.0; +http://www.exabot.com/go/robot)
			'MLBot'	=> 'MLBot',			// MLBot (www.metadatalabs.com/mlbot)
			'Twiceler' => 'Twiceler',	// Mozilla/5.0 (Twiceler-0.9 http://www.cuil.com/twiceler/robot.html)
			'Yeti' => 'Yeti',			// Yeti/1.0 (NHN Corp.; http://help.naver.com/robots/)
			'YoudaoBot' => 'YoudaoBot',	// Mozilla/5.0 (compatible; YoudaoBot/1.0; http://www.youdao.com/help/webmaster/spider/; )
			'robotgenius' => 'robotgenius', // robotgenius (http://robotgenius.net)
			'LexxeBot' => 'LexxeBot',	// LexxeBot/1.0 (lexxebot@lexxe.com)
			'Snapbot' => 'Snapbot',		// Snapbot/1.0 (Snap Shots, +http://www.snap.com)
		) as $pattern => $bot)
	{
		if(preg_match("!".$pattern."!i", $user_agent))
			return $bot;
	}

	if(preg_match("/bot|crowler/i", $user_agent))
	{
		debug_hidden_log('_need_append_data', 'unknown bot detectd');
		return 'Unknown bot';
	}

	return false;
}

function bors_client_analyze()
{
	global $client;
	$client['is_bot'] = bors_bot_detect(@$_SERVER['HTTP_USER_AGENT']);
}
