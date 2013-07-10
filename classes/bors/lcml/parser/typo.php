<?php

/**
	Всякая типографика. Подмена некоторых сочетаний ASCII-символов на типографские юникодные
	Примеры:
		Оркестр <<Боян>>
		Знание -- сила!
		Точность +/- километр.
*/

class bors_lcml_parser_typo extends bors_lcml_parser
{
	function html($text)
	{
		$text = str_replace('<<', '&laquo;', $text);
		$text = str_replace('>>', '&raquo;', $text);
		$text = str_replace(' -- ', ' &mdash; ', $text);
		$text = preg_replace("!(\s|^|\()\+/?-([^\-])!is", "$1&plusmn;$2", $text);

		return $text;
	}

	function text($text)
	{
		$text = str_replace('<<', '«', $text);
		$text = str_replace('>>', '»', $text);
		$text = str_replace(' -- ', ' — ', $text);
		$text = preg_replace("!(\s|^|\()\+/?-!is", "$1±", $text);

		return $text;
	}

	static function __unit_test($suite)
	{
		$code = 'Оркестр <<Боян>>';
		$suite->assertRegexp('#Оркестр &laquo;Боян&raquo;#', lcml($code));
		$suite->assertRegexp('#Оркестр «Боян»#', lcml($code, array('output_type' => 'text')));

		$code = 'Знание -- сила!';
		$suite->assertRegexp('#Знание &mdash; сила!#', lcml($code));

		$code = 'Точность +/- километр.';
		$suite->assertRegexp('#Точность &plusmn; километр.#', lcml($code));
		$code = '50 грамм (+/-10)';
		$suite->assertRegexp('#50 грамм \(&plusmn;10\)#', lcml($code));
		$suite->assertRegexp('#50 грамм \(±10\)#', lcml($code, array('output_type' => 'text')));
	}
}
