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
			for($p=1, $total = $x->total_pages(); $p<=$total; $p++)
			{
				echo "	<url>
		<loc>".$x->url($p)."</loc>
		<lastmod>".date('c', $x->modify_time())."</lastmod>
		<changefreq>".($p<$total ? 'yearly' : 'always')."</changefreq>
	</url>
";
			}
		}
		echo "</urlset>\n";
		return true;
	}
}
