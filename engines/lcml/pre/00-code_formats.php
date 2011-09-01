<?php

function lcml_code_formats($text)
{
	$text = preg_replace("!\{\{\{(\w+)(\s|\n)(.+?)\}\}\}!s", '[code $1]$3[/code]', $text);
	$text = preg_replace('!\{\{\{(.+?)\}\}\}!s', '[code]$1[/code]', $text);

	$text = preg_replace("!\[code=(\w+)\]!s", '[code $1]', $text);

	return $text;
}
