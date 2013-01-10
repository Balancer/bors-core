<?php

/**
	Основные консольные операции
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
		fwrite(STDOUT, $message.PHP_EOL);
	}

	static function parse(&$text)
	{
		if(!third_composer::load())
			return;

		$text = \cli\Colors::colorize($text);

		return $text;
	}
}
