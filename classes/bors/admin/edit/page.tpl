{form action=$this->url() object=$object}
<table class="{$this->layout()->table_class()}">
<tr><th align="right" width="200">Заголовок страницы:</th><td>{input name="title" class="w100p"}</td></tr>
<tr><th align="right" width="200">Текст страницы:</th><td>{textarea name="source" class="w100p" rows="20"}</td></tr>
<tr><th>&nbsp;</th><th>{submit value="Сохранить"}</th></tr>
</table>
{go value='newpage'}
{/form}
