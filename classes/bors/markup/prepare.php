<?php

class bors_markup_prepare
{
	static function parse($text)
	{
		// Rutube
		$text = preg_replace('!http://rutube.ru/tracks/\d+.html\?v=([\da-f]+)\S+!', '[rutube]$1[/rutube]', $text);

		return $text;
	}
}
