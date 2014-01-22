<?php

class url_calling2 extends url_base
{
	function url_ex($page)
	{
		$obj = $this->id();

		$url = $obj->called_url();

		if(!$url)
		{
			$url = $obj->get('url_base');
			if($url && $obj->id())
				$url .= $obj->id().'/';
		}

		if(strpos($url, '?'))
			list($url, $query) = explode('?', $url);
		else
			$query = NULL;

		if(!is_numeric($page))
			$page = NULL;

		// TODO: придумать, как бороться с URL вида http://www.aviaport.ru/404.html

		// Ссылка вида http://domain.tld/path/page.html. Вид со страницей будет http://domain.tld/path/2.html
		if(preg_match('!^(.+/)(\w+)\.html$!', $url, $m))
			$url = (!$page || $page == $obj->default_page()) ? $m[1] : $m[1].$page.'.html';
		// Ссылка вида /path/page/ Вид со страницей будет http://domain.tld/page/2.html
		elseif(preg_match('!^.+/[\w\-]+/$!', $url))
			$url = (!$page || $page == $obj->default_page()) ? $url : preg_replace('!/$!', "/{$page}.html", $url);
		// Ссылка вида http://domain.tld/path/page/ Вид со страницей будет http://domain.tld/page/2.html
		elseif(preg_match('!^http://[^/]+/$!', $url))
		{
			$url = (!$page || $page == $obj->default_page()) ? $url : preg_replace('!/$!', "/{$page}.html", $url);
		}
		// Ссылка вида http://domain.tld/path/page Вид со страницей будет http://domain.tld/page/2.html
		elseif(preg_match('!^(.+/\w+)$!', $url, $m))
		{
			$url = (!$page || $page == $obj->default_page()) ? $url : $m[1].'/'.$page.'.html';
		}

		if($query)
			$url .= '?'.$query;

		if($obj->get('in_frame'))
			$url = url_append_param($url, 'inframe', 'yes');

		if($obj->get('url_no_query'))
			$url = preg_replace('/^(.+?)\?.+$/', '$1', $url);

		return $url;
	}
}
