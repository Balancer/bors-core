<?php

class bors_cross_types extends base_list
{
	const MENTION	= '2';
	const ABOUT		= '3';
	const DELETED	= '4';

	function named_list()
	{
		return array(
			'0' => ec('Не задано'),
			'1' => ec('Автоматический'),
			self::MENTION => ec('Упоминается'),
			self::ABOUT => ec('Посвящается'),
			self::DELETED => ec('Удалено'), // Сохраняется для подавления автоматических добавлений.
			'5' => ec('Привязка'), // обычно при ручном связывании дерева объектов
		);
	}
}
