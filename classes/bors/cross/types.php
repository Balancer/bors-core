<?php

class bors_cross_types extends base_list
{
	const NONE		= '0';
	const AUTO		= '2';
	const MENTION	= '2';
	const ABOUT		= '3';
	const DELETED	= '4';
	const LINK		= '5';
	const INDIRECT	= '6';

	function named_list()
	{
		return array(
			self::NONE		=> ec('Не задано'),
			self::AUTO		=> ec('Автоматический'),
			self::MENTION	=> ec('Упоминается'),
			self::ABOUT		=> ec('Посвящается'),
			self::DELETED	=> ec('Удалено'), // Сохраняется для подавления автоматических добавлений.
			self::LINK		=> ec('Привязка'), // обычно при ручном связывании дерева объектов
			self::INDIRECT	=> ec('Косвенно'), // При автоматической привязке через другие объекты
		);
	}

	function name()
	{
		static $names = array(
			self::NONE		=> 'none',
			self::AUTO		=> 'auto',
			self::MENTION	=> 'mention',
			self::ABOUT		=> 'about',
			self::DELETED	=> 'deleted',
			self::LINK		=> 'link',
			self::INDIRECT	=> 'indirect',
		);

		return @$names[$this->id()];
	}
}
