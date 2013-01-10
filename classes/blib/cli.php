<?php

/**
	Основные консольные операции
*/

class blib_cli
{
	/**
		Ввод запрашиваемых данных с приглашением
	*/

	static function input($prompt)
	{
		fwrite(STDOUT, "$prompt: ");
		return trim(fgets(STDIN));
	}
}
