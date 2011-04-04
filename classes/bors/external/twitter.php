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
		extract($data);
		// Фикс ошибок вида http://bit.ly/gNE1ZE/ - последний слеш - ошибка.
		$text = preg_replace('!(http://bit.ly/\w+?)/!', '$1', $text);

		// http://bit.ly/gNE1ZE
		$text = preg_replace('!(http://(lnk\.ms|bit\.ly)/\w+)!e', 'bors_lib_http::url_unshort("$1", "$2");', $text);

		// http://youtu.be/sdUUx5FdySs?a
		// http://youtu.be/1SBkx-sn9i8?a
		$text = preg_replace('!(http://(youtu.be)/[^\?]+\?a)!e', 'bors_lib_http::url_unshort("$1", "$2");', $text);

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

		return NULL;

		$text = html2bb(bors_close_tags($text), array(
//			'origin_url' => $link,
			'strip_forms' => true,
		));

		return array(
			'text' => $text,
		);
	}
}
