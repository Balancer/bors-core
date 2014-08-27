<?php

class lcml_parsers_pre_markdown extends bors_lcml_parser
{
	function html($text)
	{
		$text = preg_replace('/^### (.+)$/m', '<h3>$1</h3>', $text);

		return $text;
	}

	function text($text)
	{
		return $text;
	}
}
