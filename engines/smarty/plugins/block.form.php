<?php

/**
	Старый формат. class="..." - имя класса объекта формы
	class_name - имя объекта для передачи в обработчик
*/

function smarty_block_form($params, $content, &$smarty)
{
	static $form = NULL;
	$object = bors()->main_object();

	if($content == NULL) // Открытие формы
	{
		$form = new bors_form($object);
		echo $form->html_open($params);
		return;
	}

	echo $content;
	echo $form->html_close();
}
