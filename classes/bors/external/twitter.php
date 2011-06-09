<?php

class bors_external_twitter extends bors_object
{
	static function linkify($object)
	{
		$title = $object->title();
		$url = ' '.bors_external_googl::short_url($object->url());

		$limit = 140 - bors_strlen($url);
		$text = strip_text($title, $limit, '…', true);
		return $text . $url;
	}

	static function send($user, $message)
	{
		require_once 'Services/Twitter.php';
		require_once 'HTTP/OAuth/Consumer.php';

		if(!is_object($user))
		{
			debug_hidden_log('__objects_error', 'Not object user '.$user);
			return;
		}

		$user_blog_info = bors_find_all('bors_users_blog', array(
			'user_id' => $user->id(),
			'blog_class' => __CLASS__,
			'is_active' => 1,
		));

		if(empty($user_blog_info))
			return;

		$twitter = new Services_Twitter();

		foreach($user_blog_info as $ubi)
		{
			try
			{
				$oauth   = new HTTP_OAuth_Consumer(
					config(__CLASS__.'.consumer_key'),
					config(__CLASS__.'.consumer_secret'),
					$ubi->login(),
					$ubi->password()
				);

				$twitter->setOAuth($oauth);
				$msg = $twitter->statuses->update($message);
				return $msg->id;
//				print_r($msg);
//    [id] => 34916085417521153 для http://twitter.com/#!/balabot/status/34916085417521153
			}
			catch (Services_Twitter_Exception $e)
			{
//				echo $e->getMessage();
				debug_hidden_log('twitter', 'Exception :'.$e);
			}
		}
	}

	static function parse($data)
	{
//		var_dump($data); exit();
		extract($data);
		// Фикс ошибок вида http://bit.ly/gNE1ZE/ - последний слеш - ошибка.
		$text = preg_replace('!(http://bit.ly/\w+?)/!', '$1', $text);

		// http://bit.ly/gNE1ZE
		$text = preg_replace('!(http://(lnk\.ms|bit\.ly|is\.gd)/\w+)!e', 'bors_lib_http::url_unshort("$1", "$2");', $text);

		// http://youtu.be/sdUUx5FdySs?a
		// http://youtu.be/1SBkx-sn9i8?a
		$text = preg_replace('!(http://(youtu.be)/[^\?]+\?a)!e', 'bors_lib_http::url_unshort("$1", "$2");', $text);

		$tags = array();
		if(preg_match_all('/( |^|"|«)#([\wа-яА-ЯёЁ\-]+)/um', $text, $matches))
			foreach($matches[2] as $m)
				$tags[] = common_keyword::loader($m)->title();

		if(preg_match_all('/\s\*([\wа-яА-ЯёЁ]+)/u', $text, $matches))
			foreach($matches[1] as $m)
				$tags[] = $m;

		if(preg_match('!(http://(www\.)?fresher\.ru/\d+/\d+/\d+/[^/]+/) \((.+)\)!', $text, $m))
		{
			// Это ссылка на fresher.ru
			$content = bors_lib_http::get_cached($m[1], 3600);
			$result = bors_external_fresher::parse($content, $m[1]);
			if(!$result)
				return NULL;

			$result['bb_code'] = "[quote]{$result['bb_code']}\n\n// ".$m[1]."[/quote]";
			return $result;
		}

		// balancer73: http://bit.ly/dOvjUq (Репортаж: Понять дракона) #Дагестан #Кавказ #Махачкала
		// -> balancer73: http://rusrep.ru/article/2011/01/26/report (Репортаж: Понять дракона) #Дагестан #Кавказ #Махачкала
		if(preg_match('!(http://rusrep\.ru/article/\d+/\d+/\d+/report) \(.+?\)!', $text, $m))
		{
			$content = bors_lib_http::get_cached($m[1], 3600);
			$result = bors_external_rusrep::parse($content, $m[1]);
			if(!$result)
				return NULL;

			$result['bb_code'] = "[quote]{$result['bb_code']}\n\n// ".$m[1]."[/quote]";
			return $result;
		}

//		return NULL;

		// http://twitpic.com/4vifl4 в
		// <a href="http://twitpic.com/4vifl4" title="Share photos on twitter with Twitpic"><img src="http://twitpic.com/show/thumb/4vifl4.jpg" width="150" height="150" alt="Share photos on twitter with Twitpic"></a>
		$text = preg_replace('!http://twitpic\.com/(\w+)!', 
			'<br/><a href="http://twitpic.com/$1" title="Фото из Твиттера на Twitpic"><img src="http://twitpic.com/show/thumb/$1.jpg" width="150" height="150" alt="Фото"></a><br/>',
			$text);

		$text = preg_replace('/^\w+:/', '', $text);

		$text = html2bb(bors_close_tags($text), array(
//			'origin_url' => $link,
			'strip_forms' => true,
		));

		// К этому месту у нас готовое текстовое сообщение. Нужно извлечь из него всё, что можно
		// Первый вариант — это ссылка с примечанием:
		if(preg_match('!^(http\S+)\s*(.+)$!', $text, $m))
		{
			$url = $m[1];
			$msg_text = $m[2];
			$udata = parse_url($url);
			if(preg_match('/livejournal\.com$/', $udata['host']))
				$parser = 'bors_external_livejournal';
			elseif($udata['host'] == 'bash.org.ru')
				$parser = 'bors_external_bashorgru';
			elseif($udata['host'] == 'www.aviaport.ru')
				$parser = 'bors_external_aviaport';
			else
				$parser = 'bors_external_other';

			$parsed = $parser::content_extract($url);
			if($parsed)
			{
//				var_dump($parsed); exit();
				$content = $parsed['bbshort'];
				if($ts = @$parsed['tags'])
					$tags = array_merge($tags, $ts);
			}
			else
				$content = NULL;

//			$content = html2bb($content, array(
//				'strip_forms' => true,
//			));

			if($content)
				$text = "[quote]\n{$content}\n[/quote]";
			else
				$text = "[url={$url}]{$msg_text}[/url]";

//			print_dd($text); exit();
		}

		return array(
			'text' => $text,
			'bb_code' => $text,
			'tags' => $tags,
		);
	}
}
