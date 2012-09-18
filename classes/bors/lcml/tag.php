<?php

/**
	Метакласс
	Обеспечивает возврат методов:
	html() — полнотекстовый html.
	text() — чистый текст, возможно, с markdown
	html_compact() — упрощённый html для коммуникаторов и PDA, уменьшенные картинки, ссылки на видео
	html_simple() — совершенно примитивный html для мобильных — отсутствие картинок, чистый текст с разметкой
*/

class bors_lcml_tag
{
	protected $lcml	= NULL;

	function __construct($lcml)
	{
		$this->lcml = $lcml;
	}

	function lcml($code) { return $this->lcml->parse($code); }

	// Заглушки
	function html($text, &$params) { return $text; }
	function html_compact($text, &$params) { return $this->html(); }
	function html_simple($text, &$params) { return $this->html(); }
	function text($text, &$params) { return strip_tags($this->html()); }
}
