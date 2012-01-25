<?php

function bors_bot_detect($user_agent, &$data = array())
{
	// Описания некоторых ботов: http://www.tengy.ru/bot.html

	foreach(array(
			'archive.org_bot' => 'archive.org bot',	// Mozilla/5.0 (compatible; archive.org_bot +http://www.archive.org/details/archive.org_bot)
			'Baiduspider' => 'Baidu Spider',		// Baiduspider+(+http://www.baidu.com/search/spider.htm)
													// Mozilla/5.0 (compatible; Baiduspider/2.0; +http://www.baidu.com/search/spider.html)
			'Begun Robot Crawler' => 'Begun Robot Crawler',
			'bingbot' => 'Bing',				// 207.46.195.234, Mozilla/5.0 (compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm)
			'discobot' => 'Discovery Engine',	// Mozilla/5.0 (compatible; discobot/1.1; +http://discoveryengine.com/discobot.html)
			'DotBot' => 'DotBot',		// Mozilla/5.0 (compatible; DotBot/1.1; http://www.dotnetdotcom.org/, crawler@dotnetdotcom.org)
			'Exabot' => 'Exabot',		// Mozilla/5.0 (compatible; Exabot-Images/3.0; +http://www.exabot.com/go/robot)
			'Ezooms' => 'Ezooms',		// Mozilla/5.0 (compatible; Ezooms/1.0; ezooms.bot@gmail.com)
			'Falconsbot' => 'Falconsbot',	// 219.219.127.4, Mozilla/5.0 (compatible; Falconsbot; +http://ws.nju.edu.cn/falcons/)
			'Feedreader' => 'Feedreader',	// Feedreader 3.14 (Powered by Newsbrain)
			'Gigabot' => 'Gigabot',		// Gigabot/3.0 (http://www.gigablast.com/spider.html)
			'Mediapartners-Google' => array(	//	Mediapartners-Google
				'bot' => 'Google Mediapartners',
				'crowler' => false,
			),
			'google' => 'Google',	// Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)
			'igorbot' => 'igorbot',		// Mozilla/5.0 (Windows; U; Windows NT 6.0; ru; rv:1.9.2.18) Gecko/20110614 Firefox/3.6.17 igorbot
			'LexxeBot' => 'LexxeBot',	// LexxeBot/1.0 (lexxebot@lexxe.com)
			'Liferea' => 'Liferea',		// Liferea/1.6.2 (Linux; ru_RU.UTF-8; http://liferea.sf.net/)
			'lwp' => 'LWP',				// lwp-trivial/1.41
			'Mail.Ru' => 'Mail.Ru',		// Mail.Ru/1.0
			'MJ12bot' => 'Majestic12Bot',	// Mozilla/5.0 (compatible; MJ12bot/v1.2.5; http://www.majestic12.co.uk/bot.php?+)
			'MLBot'	=> 'MLBot',			// MLBot (www.metadatalabs.com/mlbot)
			'msnbot' => 'MSN', 			// msnbot/2.0b (+http://search.msn.com/msnbot.htm)
			'NaverBot' => 'NaverBot',	// Mozilla/4.0 (compatible; NaverBot/1.0; http://help.naver.com/customer_webtxt_02.jsp)
			'Nigma' => 'Nigma',
			'Nutch'	=> 'Nutch',			// gh-index-bot/Nutch-1.0 (GH Web Search.; lucene.apache.org; gh_email at someplace dot com)
			'OOZBOT' => 'OOZBOT', 		// OOZBOT/0.20 ( http://www.setooz.com/oozbot.html ; agentname at setooz dot_com )
			'ovalebot' => 'ovalebot',	// ovalebot3.ovale.ru facepage
			'psbot'	=> 'Picsearch bot',	// psbot/0.1 (+http://www.picsearch.com/bot.html)
			'princeton crawler' => 'princeton crawler',	// nu_tch-princeton/Nu_tch-1.0-dev (princeton crawler for cass project; http://www.cs.princeton.edu/cass/; zhewang a_t cs ddot princeton dot edu)
			'rambler' => 'Rambler',
			'robotgenius' => 'robotgenius',			// robotgenius (http://robotgenius.net)
			'ROCKMELT-BOT' => 'RockMelt'	,		// ROCKMELT-BOT
			'Socialradarbot' => 'Infegy Social',	// Mozilla/5.0 (compatible; Linux; Socialradarbot/2.0; en-US; bot@infegy.com)
			'Sogou' => 'Sogou web spider',			// Sogou web spider/4.0(+http://www.sogou.com/docs/help/webmasters.htm#07)
			'SimplePie' => 'SimplePie',	// SimplePie/1.1.1 (Feed Parser; http://simplepie.org; Allow like Gecko) Build/2.00803152059E+13
			'Snapbot' => 'Snapbot',		// Snapbot/1.0 (Snap Shots, +http://www.snap.com)
			'Speedy Spider' => 'EntirewebBot',
			'Spinn3r' => 'Spinn3r',	// Mozilla/5.0 (X11; U; Linux x86_64; en-US; rv:1.9.0.19; aggregator:Spinn3r (Spinn3r 3.1); http://spinn3r.com/robot) Gecko/2010040121 Firefox/3.0.19
			'SurveyBot' => 'SurveyBot',	// 64.246.165.190, Mozilla/5.0 (Windows; U; Windows NT 5.1; en; rv:1.9.0.13) Gecko/2009073022 Firefox/3.5.2 (.NET CLR 3.5.30729) SurveyBot/2.3 (DomainTools)
			'Tagoobot' => 'Tagoobot',	// Mozilla/5.0 (compatible; Tagoobot/3.0; +http://www.tagoo.ru)
			'TurnitinBot' => 'TurnitinBot', // TurnitinBot/2.1 (http://www.turnitin.com/robot/crawlerinfo.html)
			'Twiceler' => 'Twiceler',	// Mozilla/5.0 (Twiceler-0.9 http://www.cuil.com/twiceler/robot.html)
			'Yeti' => 'Yeti',			// Yeti/1.0 (NHN Corp.; http://help.naver.com/robots/)
			'VoilaBot' => 'VoilaBot',	// Mozilla/5.0 (Windows; U; Windows NT 5.1; fr; rv:1.8.1) VoilaBot BETA 1.2 (support.voilabot@orange-ftgroup.com)
			'WebAlta' => 'WebAlta',
			'YaDirectBot' => 'YandexDirect',	// YaDirectBot/1.0
			'yahoo' => 'Yahoo',
			'YandexMetrika' => array(
				'bot' => 'YandexMetrica',
				'crowler' => false,
			),
			'yandex' => 'Yandex',
			'Yanga' => 'Yanga',
			'YoudaoBot' => 'YoudaoBot',	// Mozilla/5.0 (compatible; YoudaoBot/1.0; http://www.youdao.com/help/webmaster/spider/; )
		) as $pattern => $bot)
	{
		if(preg_match("!".$pattern."!i", $user_agent))
		{
			if(is_array($bot))
			{
				$data = $bot;
				$bot = $data['bot'];
			}
			else
				$data = array('bot' => $bot, 'crowler' => $bot);

			return $bot;
		}
	}

	if(preg_match("/bot|crowler|spider/i", $user_agent))
	{
		bors_function_include('debug/hidden_log');
		debug_hidden_log('_need_append_data', 'unknown bot detectd');
		$data = array('bot' => '$user_agent', 'crowler' => $user_agent);
		return 'Unknown bot';
	}

	if(preg_match("/monitor|feed|rss/i", $user_agent))
	{
		bors_function_include('debug/hidden_log');
		debug_hidden_log('_need_append_data', 'unknown bot detectd');
		$data = array('bot' => '$user_agent', 'crowler' => false);
		return 'Unknown bot';
	}

	return false;
}
