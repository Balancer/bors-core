<?xml version="1.0" encoding="UTF-8"?>
<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
{foreach $map as $url => $last}
	<sitemap>
		<loc>{$url}</loc>
		<lastmod>{date('c', $last->modify_time())}</lastmod>
	</sitemap>
{/foreach}
</sitemapindex>
