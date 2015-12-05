<?php

global $bors_url_titled_cache;
$bors_url_titled_cache = array();

class url_titled extends url_base
{
	function url_ex($args)
	{
		if(is_array($args))
			$page = defval($args, 'page');
		else
			$page = $args;

		global $bors_url_titled_cache;
		$obj = $this->id();

		if(!is_object($obj))
			debug_exit("Unknown url_titled '{$this->id()}'");

		if(preg_match("!^http://!", $obj->id()))
			return $obj->id();

		if($page === NULL)
			$page = $obj->page();

		list($infix, $suffix) = @$bors_url_titled_cache[$obj->internal_uri()];
		if(!$suffix)
		{
			require_once("inc/urls.php");

			$uri_name = $obj->uri_name();
			if(strlen($uri_name) > 3)
				$uri_name .= '-';

			$infix = $uri_name.$obj->id();

			if(!($suffix = substr(translite_uri_simple($obj->title()), 0, 60)))
				$suffix = '~';

			$suffix = '--'.$suffix;


			$bors_url_titled_cache[$obj->internal_uri()] = [$infix, $suffix];
		}

		if($obj->total_pages() == $page)
			$prefix = $obj->base_url().strftime("%Y/%m/", $obj->modify_time());
		elseif(method_exists($obj, 'page_modify_time'))
			$prefix = $obj->base_url().strftime("%Y/%m/", $obj->page_modify_time($page));
		else
			$prefix = $obj->base_url().strftime("%Y/%m/", $obj->create_time());

		$uri = $prefix . $infix;

		if($page && $page != 1 && $page != -1)
			$uri .= ",$page";

		$is_last_page = ($page == $obj->total_pages());// && defval($args, 'force', true); // defval($args, 'is_last_page', false);

		$url = $uri . $suffix . ($is_last_page ? '.'.($is_last_page > 1 ? $is_last_page : $obj->modify_time())%10000 : '') . ".html";

//		if(config('is_developer') && is_array($args)) { echo '?'; var_dump($args, $is_last_page, $url); exit(); }

		return $url;
//		return $uri . $suffix . ".html";
	}
}
