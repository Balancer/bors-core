Место: {$ip|geoip_flag}{$city_name}, {$country_name}

<h2>Whois</h2>
<pre>{$whois}</pre>

<h2>Запросы</h2>

{*
Array
(
    [id] => 25291643
    [user_ip] => 194.79.85.58
    [user_id] => 
    [url] => http://www.aviaport.ru/news/2014/08/27/303023.html
    [server_uri] => http://www.aviaport.ru/news/2014/08/27/303023.html
    [referer] => http://www.aviaport.ru/news/
    [class_name] => aviaport_news_view
    [object_id] => 303023
    [access_time] => 1419502518
    [operation_time] => 0.655003
    [has_bors] => 1
    [has_bors_url] => 1
    [user_agent] => Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/6.0)
    [is_bot] => 
    [is_crowler] => 
    [was_counted] => 1
)
*}
{if $requests}
<table class="{$this->layout()->table_class()}">
<tr>
	<th>url</th>
	<th>time</th>
</tr>
{foreach $requests as $x}
<tr>
	<td>{$x.url}</td>
	<td>{$x.operation_time}</td>
</tr>
{/foreach}
</table>
{/if}
