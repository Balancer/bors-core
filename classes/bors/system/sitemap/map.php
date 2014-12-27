<?php

class bors_system_sitemap_map extends bors_xml
{
	function body_data()
	{
		$class_name = $this->id();
		$sitemap_id = $this->page();

		$map = array();

		foreach(call_user_func(array($class_name, 'sitemap_index'), $sitemap_id) as $x)
		{
			$time = $x->modify_time();
			$now = time();

			if($now - $time < 7200)
				$freq = 'always';
			elseif($now - $time < 86400)
				$freq = 'hourly';
			elseif($now - $time < 86400*7)
				$freq = 'daily';
			elseif($now - $time < 86400*30)
				$freq = 'weekly';
			else
				$freq = 'monthly';

			for($p=1, $total = max(1, intval($x->get('total_pages'))); $p<=$total; $p++)
			{
				if($url=$x->url_ex($p))
				{
					$map[] = array(
						'url' => $x->url_ex($p),
						'time' => date('c', $time),
						'freq' => $freq,
					);
				}
			}
		}

		return compact('map');
	}

	function cache_static() { return rand(3600, 7200); }
}
