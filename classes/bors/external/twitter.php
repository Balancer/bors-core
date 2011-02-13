<?php

class bors_external_twitter extends bors_object
{
	static function send($user, $message)
	{
		require_once 'Services/Twitter.php';
		require_once 'HTTP/OAuth/Consumer.php';

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
				echo $e->getMessage();
			}
		}
	}

	static function parse($data)
	{
		extract($data);
		// Фикс ошибок вида http://bit.ly/gNE1ZE/ - последний слеш - ошибка.
		$text = preg_replace('!(http://bit.ly/\w+?)/!', '$1', $text);

		// http://bit.ly/gNE1ZE
		$text = preg_replace('!(http://(lnk.ms)/\w+)!e', 'bors_lib_http::url_unshort("$1", "$2");', $text);

		// http://youtu.be/sdUUx5FdySs?a
		// http://youtu.be/1SBkx-sn9i8?a
		$text = preg_replace('!(http://(youtu.be)/[^\?]+\?a)!e', 'bors_lib_http::url_unshort("$1", "$2");', $text);

		$text = html2bb(bors_close_tags($text), array(
//			'origin_url' => $link,
			'strip_forms' => true,
		));

//		var_dump($text);

		return array(
			'text' => $text,
		);
	}
}
