<?php

class common_redirect extends base_object
{
	function title()
	{
		$url = $this->id();
		if($url[0] == '/')
		{
			$data = url_parse($this->called_url());
			$url = "http://{$data['host']}$url";
		}

		//FIXME: отладить на примере http://balancer.ru/g/p2443297 (неверное имя ссылки)

//		var_dump($url); var_dump(bors_load_uri($url));

		return object_property(bors_load_uri($url), 'title', $url);
	}

	function pre_parse()
	{
		return go($this->id());
	}
}
