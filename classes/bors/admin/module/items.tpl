<table class="{$this->layout()->table_class()}">
<thead>
<tr>
{foreach $item_fields as $prop_name => $prop_title}
	{bors_pages_helper::make_sortable_th($view, $prop_name, $prop_title)}
{/foreach}
</tr>
</thead>
<tbody>
{foreach $items as $x}
<tr{if $x->get('items_list_table_row_class')} class="{join(' ', $x->get('items_list_table_row_class'))}"{/if}>
	{foreach $item_fields as $prop_name => $prop_title}
		<td>{bors_objects_helper::get_mixed_hash($x, $prop_name, $prop_title)}</td>
	{/foreach}
</tr>
{/foreach}
</tbody>
</table>
