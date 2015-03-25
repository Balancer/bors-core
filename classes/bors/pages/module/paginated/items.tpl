{if $items}
<table class="{$table_classes}">
	<thead>
		<tr>
	{foreach $item_list_fields as $p => $t}
		{$this->make_sortable_th($p, $t)}
	{/foreach}
		</tr>
	</thead>
	<tbody>
	{foreach from=$items item="x"}
		<tr{if $x->get('items_list_table_row_class')} class="{join(' ', $x->get('items_list_table_row_class'))}"{/if}>
		{foreach $item_list_fields as $p => $t}
			<td>
			{if ($p == 'imaged_titled_link' || $p == 'title') && $x->get('have_image') && $x->image()}
				{$x->image()->thumbnail('64x64')->html_code('class="float-right" style="margin-left: 10px"')}
			{/if}
			{$x->get($p)}
			</td>
		{/foreach}
		</tr>
	{/foreach}
	</tbody>
</table>

	{if $more}
<p><a href="{$more}">Все результаты</a></p>
	{/if}
{/if}
