<script type="text/javascript">{literal}function content_load(x,u,d) {
	$(x).html('<div class="box red"><b>Ждите...</b></div>').attr('disabled', 'disabled')
	$.ajax({ 
		url : u, 
		data: d,
		type: 'POST',
		success : function (data) { $(x).html(data).removeAttr('disabled') }
	})
	return false
}{/literal}</script>

{form subaction="add"}
{input name="title" value=""}
<label>{checkbox name="is_exactly" checked="0"}&nbsp;точное соответствие</label>
{hidden name="target_class_name" value=$this->real_object()->extends_class_name()}
{hidden name="target_object_id" value=$this->real_object()->id()}
{submit value="Добавить синоним"}
{/form}
<br />

<div  id="list">
<table class="{$this->layout()->table_class()}">
<tr>
	<th>ID</th>
	<th>Синоним</th>
	<th>Статус</th>
	<th>Действия</th>
</tr>
{foreach from=$list item="x"}
<tr{if $x->is_disabled()} style="color: #ccc"{else}{if $x->is_exactly()} class="b"{/if}{/if}>
	<td>{$x->id()}</td>
	<td>{$x->title()}</td>
	<td>
		{if $x->is_disabled()}
			Исключён из поиска
		{else}
			{if $x->is_auto()}Автоматический.{/if}
			{if $x->is_exactly()}Точное соответствие.{else}Морфологический поиск{/if}
		{/if}
	</td>
	<td>
{if $x->is_exactly()}{$x|imaged_link:'Разрешить неточный поиск':'unlocked'}{else}{$x|imaged_link:'Установить строгий поиск':'locked'}{/if}
{if $x->is_disabled()}{$x|imaged_link:'Разрешить':'enable'}{else}{$x|imaged_link:'Запретить':'disable'}{/if}
{$x->admin()->imaged_delete_link(false)}
{if $x->is_auto()}{$x|imaged_link:'Подтвердить':'check'}{/if}
	</td>
</tr>
{/foreach}
</table>
</div>
