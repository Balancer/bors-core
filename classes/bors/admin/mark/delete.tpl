{if class_exists('bors_moderator_note')}
<p title="{$this->id()}">
Укажите причину, по которой Вы хотите скрыть
{$this->object()|get:class_title_vp|bors_lower} 
&laquo;{$this->object()|get:titled_link}&raquo;?
</p>
{/if}

{form subaction="delete"}
{if class_exists('bors_moderator_note')}
{textarea name="note" value=""}<br/>
{/if}
{submit value="Отметить удалённым"}
{hidden name="ref" value=$this->ref()}
{/form}
