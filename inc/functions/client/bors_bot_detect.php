<?php

function bors_bot_detect($user_agent, &$data = array())
{
	// Описания некоторых ботов: http://www.tengy.ru/bot.html

	foreach(array(
			'AhrefsBot' => 'AhrefsBot',			 // Mozilla/5.0 (compatible; AhrefsBot/5.0; +http://ahrefs.com/robot/)
			'archive.org_bot' => 'archive.org bot',	// Mozilla/5.0 (compatible; archive.org_bot +http://www.archive.org/details/archive.org_bot)
			'Baiduspider' => 'Baidu Spider',	// Baiduspider+(+http://www.baidu.com/search/spider.htm)
												// Mozilla/5.0 (compatible; Baiduspider/2.0; +http://www.baidu.com/search/spider.html)
			'Begun Robot Crawler' => 'Begun Robot Crawler',
			'bingbot' => 'Bing',				// 207.46.195.234, Mozilla/5.0 (compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm)
			'BLEXBot' => 'BLEXBot',				// Mozilla/5.0 (compatible; BLEXBot/1.0; +http://webmeup.com/crawler.html)
			'Digg Feed Fetcher' => array(		// Digg Feed Fetcher 1.0 (Mozilla/5.0 (Macintosh; Intel Mac OS X 10_7_1) AppleWebKit/534.48.3 (KHTML, like Gecko) Version/5.1 Safari/534.48.3)
				'bot' => 'Digg Feed Fetcher',
				'crowler' => false,
			),
			'discobot' => 'Discovery Engine',	// Mozilla/5.0 (compatible; discobot/1.1; +http://discoveryengine.com/discobot.html)
			'DotBot' => 'DotBot',				// Mozilla/5.0 (compatible; DotBot/1.1; http://www.dotnetdotcom.org/, crawler@dotnetdotcom.org)
			'Exabot' => 'Exabot',				// Mozilla/5.0 (compatible; Exabot-Images/3.0; +http://www.exabot.com/go/robot)
			'Ezooms' => 'Ezooms',				// Mozilla/5.0 (compatible; Ezooms/1.0; ezooms.bot@gmail.com)
			'Falconsbot' => 'Falconsbot',		// 219.219.127.4, Mozilla/5.0 (compatible; Falconsbot; +http://ws.nju.edu.cn/falcons/)
			'Feedfetcher-FeedEx' => 'Feedfetcher-FeedEx',	// Feedfetcher-FeedEx.Net; (+http://feedex.net/; 1 subscriber; feed-id=39787)
			'Feedreader' => 'Feedreader',		// Feedreader 3.14 (Powered by Newsbrain)
			'Flamingo_SearchEngine' => 'Flamingo Search',	// Flamingo_SearchEngine (+http://www.flamingosearch.com/bot)
			'FTRF: Friendly robot',				// FTRF: Friendly robot/1.4
			'GeliyooBot',						// Mozilla/5.0 (compatible; GeliyooBot/1.0beta; +http://www.geliyoo.com/)
			'Gigabot' => 'Gigabot',				// Gigabot/3.0 (http://www.gigablast.com/spider.html)
			'Mediapartners-Google' => array(	//	Mediapartners-Google
				'bot' => 'Google Mediapartners',
				'crowler' => false,
			),
			'google' => 'Google',		// Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)
			'igorbot' => 'igorbot',		// Mozilla/5.0 (Windows; U; Windows NT 6.0; ru; rv:1.9.2.18) Gecko/20110614 Firefox/3.6.17 igorbot
			'LexxeBot' => 'LexxeBot',	// LexxeBot/1.0 (lexxebot@lexxe.com)
			'Liferea' => 'Liferea',		// Liferea/1.6.2 (Linux; ru_RU.UTF-8; http://liferea.sf.net/)
			'linkdexbot' => 'Linkdex Bot',  // Mozilla/5.0 (compatible; linkdexbot/2.0; +http://www.linkdex.com/about/bots/)
			'lwp' => 'LWP',				// lwp-trivial/1.41
			'MagpieRSS' => array(		//MagpieRSS/0.72 (+http://magpierss.sf.net; No cache)
				'bot' => 'MagpieRSS',
				'crowler' => false,
			),
			'Mail.Ru' => 'Mail.Ru',		// Mail.Ru/1.0
			'MJ12bot' => 'Majestic12Bot',	// Mozilla/5.0 (compatible; MJ12bot/v1.2.5; http://www.majestic12.co.uk/bot.php?+)
			'MLBot'	=> 'MLBot',			// MLBot (www.metadatalabs.com/mlbot)
			'mon.itor.us' => array(		// Mozilla/5.0 (compatible; mon.itor.us - free monitoring service; http://mon.itor.us)
				'bot' => 'mon.itor.us',
				'crowler' => false,
			),
			'montastic-monitor' => array(	// montastic-monitor http://www.montastic.com
				'bot' => 'Montastic',
				'url' => 'http://www.montastic.com/',
				'crowler' => false,
			),
			'msnbot' => 'MSN', 			// msnbot/2.0b (+http://search.msn.com/msnbot.htm)
			'NaverBot' => 'NaverBot',	// Mozilla/4.0 (compatible; NaverBot/1.0; http://help.naver.com/customer_webtxt_02.jsp)
			'Netvibes',					// Netvibes (http://www.netvibes.com/; 2 subscribers; feedID: 29108491)
			'Nigma' => 'Nigma',
			'Nutch'	=> 'Nutch',			// gh-index-bot/Nutch-1.0 (GH Web Search.; lucene.apache.org; gh_email at someplace dot com)
			'OOZBOT' => 'OOZBOT', 		// OOZBOT/0.20 ( http://www.setooz.com/oozbot.html ; agentname at setooz dot_com )
			'ovalebot' => 'ovalebot',	// ovalebot3.ovale.ru facepage
			'Page2RSS' => array(		// Mozilla/5.0 (compatible;  Page2RSS/0.7; +http://page2rss.com/)
				'bot' => 'Page2RSS',
				'crowler' => false,
			),
			'psbot'	=> 'Picsearch bot',	// psbot/0.1 (+http://www.picsearch.com/bot.html)
			'princeton crawler' => 'princeton crawler',	// nu_tch-princeton/Nu_tch-1.0-dev (princeton crawler for cass project; http://www.cs.princeton.edu/cass/; zhewang a_t cs ddot princeton dot edu)
			'proximic' => array(		// Mozilla/5.0 (compatible; proximic; +http://www.proximic.com/info/spider.php)
				'bot' => 'Proximic Spider',
				'crowler' => false,
			),
			'rambler' => 'Rambler',
			'robotgenius' => 'robotgenius',			// robotgenius (http://robotgenius.net)
			'ROCKMELT-BOT' => 'RockMelt'	,		// ROCKMELT-BOT
			'rogerbot' => array(					// rogerbot/1.0 (http://www.moz.com/dp/rogerbot, rogerbot-crawler@moz.com)
				'bot' => 'Rogerbot Crawler',
				'url' => 'http://moz.com/help/pro/rogerbot-crawler',
			),
			'RSSGraffiti' => array(					// RSSGraffiti
				'bot' => 'RSSGraffiti',
				'url' => 'http://www.rssgraffiti.com/',
				'crowler' => false,
			),
			'^SearchBot$' => array(					// SearchBot
				'bot' => 'SearchBot',
			),
			'Socialradarbot' => 'Infegy Social',	// Mozilla/5.0 (compatible; Linux; Socialradarbot/2.0; en-US; bot@infegy.com)
			'Sogou' => 'Sogou web spider',			// Sogou web spider/4.0(+http://www.sogou.com/docs/help/webmasters.htm#07)
			'SimplePie' => 'SimplePie',	// SimplePie/1.1.1 (Feed Parser; http://simplepie.org; Allow like Gecko) Build/2.00803152059E+13
			'Snapbot' => 'Snapbot',		// Snapbot/1.0 (Snap Shots, +http://www.snap.com)
			'Speedy Spider' => 'EntirewebBot',
			'Spinn3r' => 'Spinn3r',	// Mozilla/5.0 (X11; U; Linux x86_64; en-US; rv:1.9.0.19; aggregator:Spinn3r (Spinn3r 3.1); http://spinn3r.com/robot) Gecko/2010040121 Firefox/3.0.19
			'SputnikBot' => 'SputnikBot',	// Mozilla/5.0 (compatible; SputnikBot/2.2)
			'SurveyBot' => 'SurveyBot',	// 64.246.165.190, Mozilla/5.0 (Windows; U; Windows NT 5.1; en; rv:1.9.0.13) Gecko/2009073022 Firefox/3.5.2 (.NET CLR 3.5.30729) SurveyBot/2.3 (DomainTools)
			'Tagoobot' => 'Tagoobot',	// Mozilla/5.0 (compatible; Tagoobot/3.0; +http://www.tagoo.ru)
			'theoldreader' => array( 	// Mozilla/5.0 (compatible; theoldreader.com; 1 subscribers; feed-id=0719795c4d27c784217b0bc0)
				'bot' => 'The Old Reader (RSS)',
				'crowler' => false,
			),
			'Tiny Tiny RSS' => array(	// Tiny Tiny RSS/1.7.4 (http://tt-rss.org/)
				'bot' => 'Tiny Tiny RSS',
				'crowler' => false,
			),
			'TppRFbot' => array(		// TppRFbot/1.1 (compatible; http://www.ruschamber.net, http://www.geocci.com)
				'bot' => 'TppRFbot',
				'url' => 'http://www.ruschamber.net/',
			),
			'TurnitinBot' => 'TurnitinBot', // TurnitinBot/2.1 (http://www.turnitin.com/robot/crawlerinfo.html)
			'TweetedTimes' => array(	// Mozilla/5.0 (compatible; TweetedTimes Bot/1.0; +http://tweetedtimes.com)
				'bot' => 'TweetedTimes',
				'url' => 'http://tweetedtimes.com/',
				'crowler' => false,
			),
			'Twiceler' => 'Twiceler',	// Mozilla/5.0 (Twiceler-0.9 http://www.cuil.com/twiceler/robot.html)
			'Twitterbot',				// Twitterbot/1.0
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
			'ZumBot',					// Mozilla/5.0 (compatible; ZumBot/1.0; http://help.zum.com/inquiry)
		) as $pattern => $bot)
	{
		if(is_numeric($pattern))
			$pattern = $bot;

		if(preg_match("!".$pattern."!i", $user_agent))
		{
			if(is_array($bot))
			{
				$data = $bot;
				$bot = $data['bot'];
			}
			else
			{
				$data = array(
					'bot' => $bot,
					'crowler' => true,
				);
			}

			return $bot;
		}
	}

	if(preg_match("/(\w*)(bot|crowler|spider)/i", $user_agent, $m))
	{
		bors_function_include('debug/hidden_log');
		debug_hidden_log('_need_append_data', 'unknown '.$m[2].' detectd');
		$data = array(
			'bot' => $user_agent,
			'crowler' => true,
		);

		return 'Unknown '.$m[1].' '.(empty($m[1]) ? '' : ': '.$m[1]);
	}

	if(preg_match("/(monitor|feed|rss)/i", $user_agent, $m))
	{
		bors_function_include('debug/hidden_log');
		debug_hidden_log('_need_append_data', 'unknown '.$m[1].' bot detectd');
		$data = array(
			'bot' => $user_agent,
			'crowler' => false,
		);

		return 'Unknown bot';
	}

	return false;
}
