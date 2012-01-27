<?php

/**
	Класс, возвращающий JavaScript в виде document.write(...)
	содержимое html как от обычной страницы. Для JS-вставки
	модулей страниц
*/

class bors_jsh extends base_page
{
	function use_temporary_static_file() { return false; }
	function template() { return 'null.html'; }

	function pre_show()
	{
		config_set('debug_timing', false); // Чтобы не мусорить комментарием в конце JS.
		include_once("inc/js.php");
		header("Content-type: text/javascript");
		echo $this->content();
		return true;
	}

	function direct_content()
	{
		return str2js(parent::direct_content());
	}
}
