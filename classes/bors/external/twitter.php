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
}
