<?php

/**
	Основные консольные операции
	Используется composer jlogsdon/cli
*/

class blib_cli
{
	/**
		Ввод запрашиваемых данных с приглашением
	*/

	static function input($prompt, $default = NULL)
	{
		self::parse($prompt);
		fwrite(STDOUT, "$prompt: ");
		$in = trim(fgets(STDIN));

		if(!$in)
			$in = $default;

		return $in;
	}

	static function out($message)
	{
		self::parse($message);
		fwrite(STDOUT, $message);
	}

	static function parse($text)
	{
		if(!class_exists('\cli\Colors'))
			return $text;

		$text = \cli\Colors::colorize($text);

		return $text;
	}

	static function __dev()
	{
		echo blib_cli::parse('%YTest%n\n');
	}
}
