<?php

require_once('inc/bors/lists.php');

// Класс-заглушка (временная), так как пока модули по сути - обычные страницы.
class bors_module extends bors_page
{
	function html_code()
	{
		try
		{
			$content = $this->body();
		}
		catch(Exception $e)
		{
			$content = bors_lib_exception::catch_html_code($e, ec("<div class=\"red_box\">Ошибка модуля ").$this->class_name()."</div>");
		}

		return $content;
	}

	static function show($class_name, $args)
	{
		echo $mod = bors_load_ex($class_name, NULL, $args);
		echo $mod->html_code();
	}
}
