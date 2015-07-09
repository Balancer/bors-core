{if $bootstrap}

<form class="form-inline form-search text-center" action="{$admin_search_url}">
	<div class="input-append">
		<input type="text" class="span4 search-query" name="q" value="{$query|htmlspecialchars}" placeholder="Введите подстроку для поиска" />
		<button type="submit" class="btn">Искать</button>
	</div><br/>
{if $search_where}
	{bors_radio delim=' ' name="w" value=$w list=$search_where}
{/if}
</form>

{else}

<form class="admin-top-search" action="{$admin_search_url}" style="margin-bottom: 10px">
<fieldset>
<legend>Поиск:</legend>
<center>
	<label>Искать:</label> <input type="text" name="q" size="40" value="{$query|htmlspecialchars}" />
{if $search_where}
	{bors_radio delim=' ' name="w" value=$w list=$search_where}
{/if}
	<input type="submit" class="search-submit" value="Искать" />
</center>
</fieldset>
</form>

{/if}
