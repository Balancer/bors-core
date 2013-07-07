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

//TODO: придумать, как бороться с URL вида http://www.aviaport.ru/404.html
		if(preg_match('!^(.+/)\w+\.html$!', $url, $m))
			$url = (!$page || $page == $obj->default_page()) ? $m[1] : $m[1].$page.'.html';
		elseif(preg_match('!^.+/[\w\-]+/$!', $url))
			$url = (!$page || $page == $obj->default_page()) ? $url : preg_replace('!/$!', "/{$page}.html", $url);
		elseif(preg_match('!^http://[^/]+/$!', $url))
		{
			$url = (!$page || $page == $obj->default_page()) ? $url : preg_replace('!/$!', "/{$page}.html", $url);
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
