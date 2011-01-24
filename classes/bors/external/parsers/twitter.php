<?php

class bors_external_parsers_twitter extends bors_object
{
/*
	balancer73: &#9829; Свободная частица by Тимур Шаов #lastfm: http://bit.ly/fGzrCQ
	balancer73: Мне понравилось видео YouTube -- Прикол http://youtu.be/aGUstji0Pz8?a
	balancer73: Вах! Да я же теперь и из Твиттера могу на Авиабазу писать! :) В общем, если кому нужно - http://balancer.ru/_bors/igo?o=forum_post__2221049
*/
	function __construct($text)
	{
		parent::__construct($text);

		$this->set_title = NULL;

		if(preg_match_all('/ #(\w+)/', $text, $m))
		{
			$this->set_keywords($m[1], false);
		}

		$this->set_text($text, false);
	}
}
