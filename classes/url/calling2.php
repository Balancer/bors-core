<?php

class url_calling2 extends url_base
{
	function url($page = NULL)
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

		if(preg_match('!^(.+/)\w+\.html$!', $url, $m))
			$url = (!$page || $page == $obj->default_page()) ? $m[1] : $m[1].$page.'.html';
		elseif(preg_match('!^.+/[\w\-]+/$!', $url))
			$url = (!$page || $page == $obj->default_page()) ? $url : preg_replace('!/$!', "/{$page}.html", $url);
		elseif(preg_match('!^http://[^/]+/$!', $url))
			$url = (!$page || $page == $obj->default_page()) ? $url : preg_replace('!/$!', "/{$page}.html", $url);

		if($query)
			$url .= '?'.$query;

		if($obj->get('in_frame'))
			$url = url_append_param($url, 'inframe', 'yes');

		return $url;
//		debug_exit("Unknown calling url format: '{$url}' for {$this->id()->class_name()}({$this->id()->id()})");
	}
}
