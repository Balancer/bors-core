<?php

/**
	Показ блока текста в виде предупреждения.
	Категории: оформление, блоки, текст
	Пример использования: [warning]Javascript запрещён[/warning]
*/

// Отладка/проверка: http://www.balancer.ru/g/p2963799
class lcml_tag_pair_warning extends bors_lcml_tag_pair
{
	function html($text)
	{
		return "<div class=\"yellow_box\">".lcml($text)."</div>";
	}
}
