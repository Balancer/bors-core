<?php

/*
	Возвращает список sitemap-файлов конкретных объектов с разбивкой
	по некоторому sitemap-id. Например, по номеру страниц или по дате
*/

class bors_system_sitemap_class extends bors_page
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
				$ids = call_user_func(array($class_name, 'sitemap_ids'));
//				~r($ids);

				foreach($ids as $id)
				{
					$last = call_user_func(array($class_name, 'sitemap_last_modified'), $id);

					if(!$last)
						continue;

					echo "	<sitemap>
		<loc>http://{$_SERVER['HTTP_HOST']}/sitemap-{$class_name}-{$id}.xml</loc>
		<lastmod>".date('c', $last->modify_time())."</lastmod>
	</sitemap>
";
				}
			}
		}

		echo "</sitemapindex>\n";

		return true;
	}

	function cache_static() { return rand(300, 1200); }
}
