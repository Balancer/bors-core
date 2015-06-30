<ul>
<li>Общая загрузка: {$total_time|round} за {$period} сек. ({math equation="1.0*x/y" x=$total_time y=$period assign="r"}{$r|round:2}:1)</li>
</ul>

<h2>Загрузка по пользователям</h2>
<table class="{$this->layout()->table_class()}">
<thead><tr>
	<th>user ip</th>
	<th>user id</th>
	<th>всего обращений</th>
	<th>потрачено секунд</th>
	<th>%</th>
	<th>бот</th>
	<th>user agent</th>
</tr></thead>
<tbody>
{foreach from=$max_cpu_by_user item="x"}
<tr{if $x.user_id} class="green"{elseif $x.is_crawler} class ="red b"{elseif $x.is_bot} class="orange"{/if}>
	<td class="nobr">{$x.user_ip|geoip_flag}{if $can_see_ip}<a href="/_bors/admin/reports/ip?ip={$x.user_ip}">{$x.user_ip}</a>{else}xxx.xxx.xxx.xxx{/if}</td>
	<td>{$x.user_id}</td>
	<td>{$x.cnt}</td>
	<td>{$x.su|round:2}</td>
	<td>{math equation="time / total * 100" time=$x.su total=$total_time assign="perc"}{$perc|round:2}</td>
	<td>{$x.is_bot}</td>
	<td>{$x.user_agent}</td>
</tr>
{/foreach}
</tbody>
</table>

<h2>Загрузка по классам</h2>
<table class="{$this->layout()->table_class()}">
<thead>
<tr><th>class name</th>
	<th>max uri</th>
	<th>referer</th>
	<th>всего обращений</th>
	<th>потрачено секунд</th>
	<th>%</th>
</tr>
</thead>
<tbody>
{foreach from=$max_cpu_by_classes item="x"}
<tr><td>{$x.class_name}</td>
	<td><a href="{$x.uri}">{$x.uri|wordwrap:80:" ":true}</a></td>
	<td>{$x.referer|host_link}</td>
	<td>{$x.cnt}</td>
	<td>{$x.su|round:2}</td>
	<td>{math equation="time / total * 100" time=$x.su total=$total_time assign="perc"}{$perc|round:2}</td>
</tr>
{/foreach}
</tbody>
</table>

<h2>Загрузка комбинированная</h2>
<table class="{$this->layout()->table_class()}">
<thead>
<tr><th>user ip</th>
	<th>user id</th>
	<th>class name</th>
	<th>всего обращений</th>
	<th>потрачено секунд</th>
	<th>%</th>
	<th>бот</th>
	<th>user agent</th>
</tr>
</thead>
<tbody>
{foreach from=$max_cpu_by_combine item="x"}
<tr><td class="nobr">{$x.user_ip|geoip_flag}{if $can_see_ip}<a href="/_bors/admin/reports/ip?ip={$x.user_ip}">{$x.user_ip}{else}xxx.xxx.xxx.xxx{/if}</td>
	<td>{$x.user_id}</td>
	<td>{$x.class_name}</td>
	<td>{$x.cnt}</td>
	<td>{$x.su|round:2}</td>
	<td>{math equation="time / total * 100" time=$x.su total=$total_time assign="perc"}{$perc|round:2}</td>
	<td>{$x.is_bot}</td>
	<td>{$x.user_agent}</td>
</tr>
{/foreach}
</tbody>
</table>

<h2>Тяжёлые ссылки</h2>
<table class="{$this->layout()->table_class()}">
<thead>
<tr>
	<th>Ссылка</th>
	<th>Время исполнения</th>
	<th>Время запроса</th>
	<th>Объект</th>
</tr>
</thead>
<tbody>
{foreach $heavy_links as $x}
<tr>
	<td><a href="{$x.url}" target="_blank">{$x.url}</a></td>
	<td>{$x.operation_time}</td>
	<td>{$x.access_time|date:'H:i:s'}</td>
	<td>{$x.class_name}({$x.object_id})</td>
</tr>
{/foreach}
</tbody>
</table>
