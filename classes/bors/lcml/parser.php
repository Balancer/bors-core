<?php

/**
	Метакласс парсеров. Отчасти эквивалентен bors_lcml_tag
*/

class bors_lcml_parser
{
	function html($text) { return $text; }
	function html_compact($text) { return $this->html(); }
	function html_simple($text) { return $this->html(); }
	function text($text) { return strip_tags($this->html()); }

	function parse($text, $lcml)
	{
		switch($lcml->output_type())
		{
			case 'text':
				return $this->text($text);
			case 'html_compact':
				return $this->html_compact($text);
			case 'html_simple':
				return $this->html_simple($text);
			case 'html':
			default:
				return $this->html($text);
		}
	}
}
