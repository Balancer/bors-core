<?php

class bors_system_sitemap_index extends bors_page
{
	function pre_show()
	{
		debug_hidden_log('sitemap', 'index');

		header("Content-Type: application/xml; charset=utf-8");
		echo '<?xml version="1.0" encoding="UTF-8"?>
<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
';
		if(config('sitemap_classes'))
		{
			foreach(explode(' ', config('sitemap_classes')) as $class_name)
			{
				$total = call_user_func(array($class_name, 'sitemap_total'), $_SERVER['HTTP_HOST']);
				$pages = ceil($total/500);
				for($p = $pages; $p>=1; $p--)
				{
					$time = call_user_func(array($class_name, 'sitemap_last_modify_time'), $_SERVER['HTTP_HOST'], $p, 500);
					echo "	<sitemap>
		<loc>http://{$_SERVER['HTTP_HOST']}/sitemap-{$class_name}-{$p}.xml</loc>
		<lastmod>".date('c', $time)."</lastmod>
	</sitemap>
";
				}
			}
		}

		echo "</sitemapindex>\n";

		return true;
	}

	function cache_static() { return rand(600, 1200); }
}
