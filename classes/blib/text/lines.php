<?php

/**
	Набор функций для операций над строками текста
*/

class blib_text_lines
{
	static function move_up($text, $line)
	{
		if($line < 1)
			return $text;

		$ss = explode("\n", $text);
		list($ss[$line], $ss[$line-1]) = array($ss[$line-1], $ss[$line]);
		return join("\n", $ss);
	}

	static function move_down($text, $line)
	{
		$ss = explode("\n", $text);
		if($line+1 >= count($ss))
			return $text;

		list($ss[$line], $ss[$line+1]) = array($ss[$line+1], $ss[$line]);
		return join("\n", $ss);
	}
}
