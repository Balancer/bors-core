<?php

/**
	Основные консольные операции
	Используется composer jlogsdon/cli
*/

require_once('composer/vendor/autoload.php');

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
		$message = self::parse($message);
		fwrite(STDOUT, $message);
	}

	static function parse($text)
	{
		if(!class_exists('\cli\Colors'))
		{
			echo "Please do composer require jlogsdon/cli=*\n";
			return $text;
		}

		$text = \cli\Colors::colorize($text);

		return $text;
	}

	static function __dev()
	{
		blib_cli::out('%YTest%n\n');
	}
}
