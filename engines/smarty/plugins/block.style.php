<?php

/**
	Аналогично <style>...</style> html в head.
*/



function smarty_block_style($params, $content, &$smarty)
{
	if($content == NULL) // Открытие блока
	{
		// Ничего не делаем
		return;
	}

	// Закрытие блока.
	template_style($content);
	return;
}
