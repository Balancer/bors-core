<?php

class url_calling extends url_base
{
	function url_ex($page)
	{
		$url = NULL;
		$obj = $this->id();
		if(!$obj || !is_object($obj))
			debug_hidden_log('NPE', 'not object: '.$obj);
		else
			$url = $obj->called_url();

		if(preg_match('!^(.+/\w+),\w+/$!', $url, $m))
			return (!$page || $page == $obj->default_page()) ? $m[1].'/' : $m[1].','.$page.'/';

		if(preg_match('!^.+/\w+/$!', $url))
			return (!$page || $page == $obj->default_page()) ? $url : preg_replace('!/$!', ",{$page}/", $url);

		return $url;
//		debug_exit("Unknown calling url format: '{$url}' for {$this->id()->class_name()}({$this->id()->id()})");
	}
}
