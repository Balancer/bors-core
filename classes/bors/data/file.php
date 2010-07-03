<?php

/**
	Пример использования:
	$mail = bors_data_file::load('mails/registered.markdown');
	bors_mail::send($user, $mail);
*/

class bors_data_file extends bors_object
{
	// Читаем содержимое файла из одного из data-каталогов в bors_dirs()
	static function read($file)
	{
		if(file_exists($file) && is_readable($file))
			return file_get_contents($file);

		foreach(bors_dirs() as $dir)
			if(file_exists($fn = $dir.'/data/'.$file) && is_readable($fn))
				return file_get_contents($fn);

		return NULL;
	}

	// Загружаем файл как один из видов markup
	// Сейчас - только markdown. Нужно сделать позже автоопределение:
	//		1. По расширению
	//		2. По контенту
	static function load($file, $markup = NULL)
	{
		$text = self::read($file);
		if(is_null($text))
			return NULL;

		$x = bors_markup_markdown::factory($text);
		return $x;
	}
}
