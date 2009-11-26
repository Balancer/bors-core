<?php

class bors_markup_markdown
{
	static function parse($text)
	{
		require_once(config('markdown_include'));
		return Markdown($text);
	}
}
