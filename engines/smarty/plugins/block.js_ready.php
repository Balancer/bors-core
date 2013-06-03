<?php

/**
	Подставляет указанный код в блок, вызывающийся после инициализации документа,
	как правило — через jQuery

	Пример использования:
	{js_ready}
	$('.tooltip').tooltip()
	{/js_ready}
*/



function smarty_block_js_ready($params, $content, &$smarty)
{
	if($content == NULL) // Открытие блока
	{
		// Ничего не делаем
		return;
	}

	// Закрытие блока.
	jquery::on_ready($content);
	return;
}
