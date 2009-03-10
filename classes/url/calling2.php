<?php

class url_calling2 extends url_base
{
	function url($page = NULL)
	{
		$obj = $this->id();
		$url = $obj->called_url();
		
		if(preg_match('!^(.+/)\w+\.html$!', $url, $m))
			return (!$page || $page == $obj->default_page()) ? $m[1] : $m[1].$page.'.html';

		if(preg_match('!^.+/\w+/$!', $url))
			return (!$page || $page == $obj->default_page()) ? $url : preg_replace('!/$!', "/{$page}.html", $url);

		if(preg_match('!^http://[^/]+/$!', $url))
			return (!$page || $page == $obj->default_page()) ? $url : preg_replace('!/$!', "/{$page}.html", $url);

		return $url;
//		debug_exit("Unknown calling url format: '{$url}' for {$this->id()->class_name()}({$this->id()->id()})");
	}
}
