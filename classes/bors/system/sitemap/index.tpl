<?xml version="1.0" encoding="UTF-8" ?>
<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
{foreach $class_data as $class_name => $last}
	<sitemap>
		<loc>{$last->sitemap_class_index_url()}</loc>
		<lastmod>{date('c', $last->modify_time())}</lastmod>
	</sitemap>
{/foreach}
</sitemapindex>
