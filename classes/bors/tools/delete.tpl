<p title="{$this->id()}">
Вы уверены, что хотите удалить
{$this->object()|get:class_title_vp}
{if $this->object()->title()}&laquo;{$this->object()|get:titled_link}&raquo;{/if}?
</p>

{form action="/_bors/admin/delete/" act="delete"}
{hidden name="object" value=$this->object()->internal_uri_ascii()}
{hidden name="ref" value=$this->ref()}
{hidden name="no_ref" value=$smarty.server.HTTP_REFERER}
{submit name="no" value="Нет"}
{submit name="yes" value="Да"}
{/form}
