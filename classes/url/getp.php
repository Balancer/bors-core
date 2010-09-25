<?php

class url_getp extends url_base
{
	function url($page = NULL)
	{
		$obj = $this->id();
		$url = $obj->called_url_no_get();

		$get = $_GET;

		if($page && $page != 1)
			$get['p'] = $page;
		else
			unset($get['p']);

		$pars = array();
		$skip_keys = explode(',', $obj->get('url_skip_keys'));
		$use_keys = explode(',', $obj->get('url_use_keys'));

		if($use_keys && $use_keys[0])
		{
			foreach($use_keys as $k)
			{
				if(!$k || $k == 'p')
					continue;

				if(preg_match('/^(\w+)\[\]$/', $k, $m))
					$k = $m[1];

				$v = $obj->get($k);

				if(is_array($v))
					$v = join(',', array_map('urlencode', $v));
				else
					$v = urlencode($v);

				if(!$v)
					continue;

				$pars[] = urlencode($k).'='.$v;
			}

			if($page && $page != 1)
				$pars[] = 'p='.$page;
		}
		else
		{
			foreach($get as $k => $v)
			{
				if(!$k || in_array($k, $skip_keys))
					continue;

				if(!$v)
					continue;

				if(is_array($v))
					$pars[] = urlencode($k).'='.urlencode(join(',', $v));
				else
					$pars[] = urlencode($k).'='.urlencode($v);

			}
		}

		if($pars)
			return $url.'?'.join('&', $pars);

		return $url;
	}
}
