{block name="body_header"}

{if $new_link_title}
	{if not $bootstrap}
<ul>
<li><a href="{$new_link_url}">{$new_link_title}</a></li>
</ul>
	{/if}
{/if}

{if $admin_search_url && !$this->get('have_page_search')}
{include file='xfile:main.search.tpl' search_where=$search_where}
{/if}

{$this->get('content_before_table')}
{/block}

{if not $this->get('skip_top_pagination')}
{$pagination}
{/if}

{if $bootstrap}
	{if $new_link_title && count($items) > 15}
		<a href="{$this->new_object_url()}" class="btn btn-primary" style="margin-bottom: 8px">{$new_link_title}</a>
	{/if}
{/if}

{if $items}
<table class="{$this->layout()->table_class()}">
<thead>
	<tr>
{foreach $item_fields as $prop_name => $prop_title}
	{$this->make_sortable_th($prop_name, $prop_title)}
{/foreach}
	</tr>
</thead>
<tbody>
{foreach from=$items item="x"}
	<tr{if $x->get('items_list_table_row_class')} class="{join(' ', $x->get('items_list_table_row_class'))}"{/if}>
	{foreach $item_fields as $prop_name => $prop_title}
		{$val=bors_objects_helper::get_mixed_hash($x, $prop_name, $prop_title)}
		{if is_array($val)}
		<td{if $val.html5_data && $val.html5_data.sort} data-sort="{$val.html5_data.sort}"{/if}>{$val.value}</td>
		{else}
		<td>{$val}</td>
		{/if}
	{/foreach}
	</tr>
{/foreach}
</tbody>
</table>
{/if}

{if $new_link_title}
	{if $bootstrap}
		<a href="{$this->new_object_url()}" class="btn btn-primary">{$new_link_title}</a>
	{/if}
{/if}

{$pagination}

{block name="append"}
{/block}

