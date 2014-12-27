<?php

/*
	Возвращает sitemap.xml со списком файлов с sitemap конкретных классов.
	Например: http://forums.balancer.ru/sitemap-index.xml
*/

class bors_system_sitemap_index extends bors_page
{
	function pre_show()
	{
		header("Content-Type: application/xml; charset=utf-8");
		echo '<?xml version="1.0" encoding="UTF-8"?>
<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
';
		if(config('sitemap.classes', config('sitemap_classes')))
		{
			foreach(explode(' ', config('sitemap_classes')) as $class_name)
			{
				$last = bors_find_first($class_name, array('order' => '-modify_time'));

				echo "	<sitemap>
		<loc>http://{$_SERVER['HTTP_HOST']}/sitemap-{$class_name}.xml</loc>
		<lastmod>".date('c', $last->modify_time())."</lastmod>
	</sitemap>
";
			}
		}

		echo "</sitemapindex>\n";

		return true;
	}

	function cache_static() { return rand(60, 120); }
}
