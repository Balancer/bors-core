<?php

class url_calling extends url_base
{
	function url($page=1)
	{
		$url = $this->id()->called_url();
		
		if(preg_match('!^(.+/\w+),\w+/$!', $url, $m))
			return $page == 1 ? $m[1].'/' : $m[1].','.$page.'/';

		if(preg_match('!^.+/\w+/$!', $url))
			return $page == 1 ? $url : preg_replace('!/$!', ",{$page}/", $url);

		return $url;
//		debug_exit("Unknown calling url format: '{$url}' for {$this->id()->class_name()}({$this->id()->id()})");
	}
}
