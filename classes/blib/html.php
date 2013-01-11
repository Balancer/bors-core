<?php

class blib_html
{

	public static function close_tags($html)
	{
		if(function_exists('DOMDocument'))
			return self::__close_tags_new($html);

		return self::__close_tags_old2($html);
	}

	private static function __close_tags_new($html)
	{
		$dom = new DOMDocument('1.0', 'utf-8');
		@$dom->loadHTML('<html><meta http-equiv="Content-Type" content="text/html; charset=utf-8"><body>' . $html . '</body></html>');
		$html = $dom->saveHTML();
		$html = preg_replace("!^.+?</head>!is", "", $html);
		$html = preg_replace("/\<\/?(body|html|p)>/i", "", $html);
		return trim($html);
	}

	private static function __close_tags_old2($html)
	{
	    $single_tags = array('meta','img','br','link','area','input','hr','col','param','base');
	    preg_match_all('~<([a-z0-9]+)(?: [^>]*)?(?<![/|/ ])>~iU', $html, $result);
	    $openedtags = $result[1];
	    preg_match_all('~</([a-z0-9]+)>~iU', $html, $result);
	    $closedtags = $result[1];
	    $len_opened = count($openedtags);

	    if (count($closedtags) == $len_opened)
	        return $html;

	    $openedtags = array_reverse($openedtags);
	    for ($i=0; $i < $len_opened; $i++)
	    {
	        if (!in_array($openedtags[$i], $single_tags))
	        {
	            if (FALSE !== ($key = array_search($openedtags[$i], $closedtags)))
	                unset($closedtags[$key]);
	            else
	                $html .= '</'.$openedtags[$i].'>';
	        }
	    }

	    return $html;
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