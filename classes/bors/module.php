<?php

require_once('inc/bors/lists.php');

// Класс-заглушка (временная), так как пока модули по сути - обычные страницы.
class bors_module extends bors_page
{
	function html_code()
	{
		return $this->body();
	}
}
