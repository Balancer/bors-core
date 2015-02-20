<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
{foreach $map as $x}
	<url>
		<loc>{$x.url}</loc>
		<lastmod>{$x.time}</lastmod>
		<changefreq>{$x.freq}</changefreq>
	</url>
{/foreach}
</urlset>
