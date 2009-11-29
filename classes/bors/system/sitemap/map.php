<?php

class bors_system_sitemap_map extends bors_page
{
	function pre_show()
	{
		$class_name = $this->id();
		$page = $this->page();

		header("Content-Type: application/xml; charset=utf-8");
		echo '<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
';
		foreach(call_user_func(array($class_name, 'sitemap_index'), $_SERVER['HTTP_HOST'], $page, 1000) as $x)
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
					$freq = 'montly';

			for($p=1, $total = $x->total_pages(); $p<=$total; $p++)
			{


				echo "	<url>
		<loc>".$x->url($p)."</loc>
		<lastmod>".date('c', $time)."</lastmod>
		<changefreq>{$freq}</changefreq>
	</url>
";
			}
		}
		echo "</urlset>\n";
		return true;
	}
}
