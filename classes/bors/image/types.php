<?php

class bors_image_types extends base_list
{
	function named_list()
	{
		return array(
			'0' => ec('<не указано>'),
			'1' => ec('Фотография'),
			'2' => ec('Логотип'),
			'3' => ec('Коллаж'),
			'4' => ec('Прочее'),
		);
	}
}
