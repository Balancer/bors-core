{if method_exists($object, 'common_themes')}
{form act='theme'}
{module class="aviaport_admin_common_themes_module" object=$this->real_object()}
{submit value="Сохранить"}
{go value=$request_url}
{/form}
{/if}

<br/><br/>
<h3>Прямые связи</h3>
<script type="text/javascript">{literal}function content_load(x,u,d) {
	$(x).html('<div class="box red"><img src="/_bors/i/wait-16.gif" style="vertical-align:middle;"/> <b>Ждите...</b></div>').attr('disabled', 'disabled')
	$.ajax({ 
		url : u, 
		data: d,
		type: 'POST',
		success : function (data) { $(x).html(data).removeAttr('disabled') }
	})
	return false
}{/literal}</script>

{form act="link"}
{dropdown name="link_class_name" list="array('' => '', 
		'aviaport_digest_atom' => 'Новость', 
		'aviaport_image' => 'Фото',
		'aviaport_event' => 'Событие', 
		'aviaport_digest_story' => 'Сюжет',
	);" value=""}
№
{input name="link_object_id" value=""}
тип: {dropdown name="link_type_id" list="bors_cross_types" value=""}
{submit value="Добавить привязку"}
{hidden name="object" value=$object_uri}
{/form}
<br />

{if not $object->get('skip_auto_search_links')}
{form act="search"}
<input type="submit" value="Автоматический пересчёт и поиск связей"
	onClick="return content_load('#crosslinks', '/get/crosslinks.bas', {ldelim} obj : '{$object_uri}' {rdelim} )"
/>
{/form}
{/if}

{$this->layout()->mod('pagination')}

<div  id="crosslinks">
<table class="{$this->layout()->table_class()}">
<tr>
	<th>Раздел</th>
	<th>Тип связи</th>
	<th>Материал</th>
	<th>Дата создания</th>
	<th>Дата модификации</th>
	<th>Действия</th>
</tr>
{foreach from=$cross item="x"}
{assign var="cross_type" value=$x->link_type_id()}
<tr>
	<td>{$x->class_title()}</td>
	<td>{if $cross_type}
			{$cross_type|abs|bors_list_item_name:bors_cross_types|strtolower}
			{if $x->bors_link()->is_auto()} (авто){/if}
		{/if}
	</td>
{if $x->link_type_abs_id() == 3}
	<td><b>{$x->admin()->imaged_titled_link()}</b></td>
{else}
	<td>{$x->admin()->imaged_titled_link()}</td>
{/if}
	<td>{$x->create_time()|short_time}</td>
	<td>{$x->modify_time()|short_time}</td>
	<td>
{assign var='url' value='/admin/cross_chtype?from='|cat:$object_uri|cat:'&to='|cat:$x->internal_uri_ascii()}
{$x|imaged_link:'сменить тип':$url:'edit'}
{assign var='url' value='/admin/cross_unlink?from='|cat:$object_uri|cat:'&to='|cat:$x->internal_uri_ascii()}
{$x|imaged_link:'убрать привязку':$url:'delete'}
	</td>
</tr>
{/foreach}
</table>
</div>

{$this->layout()->mod('pagination')}

