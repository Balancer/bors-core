<?php

class url_getp extends url_base
{
	function url($page = NULL)
	{
		$obj = $this->id();
		$url = $obj->called_url();

		$get = $_GET;

		if($page && $page != 1)
			$get['p'] = $page;
		else
			unset($get['p']);

		$pars = array();
		foreach($get as $k => $v)
			$pars[] = urlencode($k).'='.urlencode($v);

		if($pars)
			return $url.'?'.join('&', $pars);

		return $url;
	}
}
