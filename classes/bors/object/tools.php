<?php

class bors_object_tools extends base_empty
{
	function object() { return $this->id(); }

	function favorite_link_ajax() { return $this->favorite_link(true); }

	function favorite_link($is_ajax = false)
	{
		$f = bors_user_favorite::find(bors()->user(), $this->object());
		$target_title = $this->object()->object_title();
		$target_uri = $this->object()->internal_uri_ascii();

		if($f) // Избранное найдено. Описываем удаление.
		{
			$img = '/_bors/i16/favorite.png';
			$title = ec('Удалить ').$target_title.ec(' из Вашего избранного');
			$url = '/_bors/tools/favorites?op=remove&object='.$target_uri;
		}
		else // Не найдено. Значит, ссылка на добавление.
		{
			$img = '/_bors/i16/favorite_gray.png';
			$title = ec('Добавить ').$target_title.ec(' в Ваше избранное');
			$url = '/_bors/tools/favorites?op=add&object='.$target_uri;
		}

		if(!$is_ajax)
			return "<a href=\"$url\"><img src=\"$img\" width=\"16\" height=\"16\" title=\"".htmlspecialchars($title)."\"/></a>";

		if($f)
			$aurl = '/_bors/tools/favorites/ajax?op=remove&object='.$target_uri;
		else
			$aurl = '/_bors/tools/favorites/ajax?op=add&object='.$target_uri;

		return "<a href=\"$aurl\" onclick=\"return bors_ajax_click(this.href)\"><img id=\"favo_{$target_uri}\" src=\"$img\" width=\"16\" height=\"16\" title=\"".htmlspecialchars($title)."\" alt=\"F\"/></a>";
	}

	function use_ajax()
	{
		$this->object()->add_template_data('use_jQuery', true);
		$this->object()->add_template_data_array('js_include', '/_bors/js/bors.js');
	}
}
