<?php

/**
	Старый формат. class="..." - имя класса объекта формы
	class_name - имя объекта для передачи в обработчик
*/

function smarty_block_form($params, $content, &$smarty)
{
	static $form = NULL;
	$params['calling_object'] = defval($params, 'calling_object', bors()->main_object());

	if($content == NULL) // Открытие формы
	{
		$form = new bors_form(NULL);
		echo $form->html_open($params);
		if($form->attr('has_autofields'))
			$smarty->assign('has_autofields', true);

		return;
	}

	echo $content;
	echo $form->html_close();
}
