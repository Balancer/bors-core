<?php

function lp_script($url)
{
	if(preg_match('!^http://(www.blogpoll.com|modpoll.com)/!', $url))
		return "<script type=\"text/javascript\" src=\"$url\"></script>";

	return "[script]{$url}[/script]";
}
