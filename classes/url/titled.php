<?php

global $bors_url_titled_cache;
$bors_url_titled_cache = array();

class url_titled extends url_base
{
	function url($page=NULL)
	{
		global $bors_url_titled_cache;
	
		if(preg_match("!^http://!", $this->id()->id()))
			return $this->id()->id();
			
		if($page === NULL)
			$page = $this->id()->page();

		@list($prefix, $suffix) = @$bors_url_titled_cache[$this->id()->internal_uri()];
		if(!$prefix)
		{
			require_once("funcs/modules/uri.php");
			$prefix = $this->id()->base_url().strftime("%Y/%m/%d/", $this->id()->modify_time());
			$prefix .= $this->id()->uri_name()."-".$this->id()->id();

			$suffix = "--".substr(translite_uri_simple($this->id()->title()), 0, 40).'.'.$this->id()->modify_time().".html"; 
			
			$bors_url_titled_cache[$this->id()->internal_uri()] = array($prefix, $suffix);
		}

		$uri = $prefix;
		
		if($page && $page != 1 && $page != -1)
			$uri .= ",$page";

		return $uri . $suffix;
	}
}
