<?php

class blib_html
{
	function close_tags($html)
	{
		$dom = new DOMDocument('1.0', 'utf-8');
		@$dom->loadHTML('<html><meta http-equiv="Content-Type" content="text/html; charset=utf-8"><body>' . $html . '</body></html>');
		$html = $dom->saveHTML();
		$html = preg_replace("!^.+?</head>!is", "", $html);
		$html = preg_replace("/\<\/?(body|html|p)>/i", "", $html);
		return trim($html);
	}

	static function __unit_test($suite)
	{
		$suite->assertEquals('<i><b>text</b></i>', blib_html::close_tags('<i><b>text</b>'));
		$suite->assertEquals('<b>text</b>', blib_html::close_tags('<b>text</b></s>'));
		$suite->assertEquals('<div><blockquote class="q"> test<hr></blockquote></div>', blib_html::close_tags('<div ><blockquote class="q"> test<hr/> </p ></blockquote>'));

		$suite->assertEquals('<blockquote><div>Бывший министр</div></blockquote>', blib_html::close_tags('<blockquote><div>Бывший министр'));

		$suite->assertEquals(file_get_contents(dirname(__FILE__).'/html.unittest.out.text'), blib_html::close_tags(file_get_contents(dirname(__FILE__).'/html.unittest.data.text')));
	}
}