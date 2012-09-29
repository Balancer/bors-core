<?php

class blib_html
{
	function close_tags($html)
	{
		$html = self::close_opened_tags($html);
		$html = self::close_closed_tags($html);
		return $html;
	}

	// via http://rmcreative.ru/blog/post/zakryt-nezakrytye-tegi
	function close_opened_tags($html)
	{
		$single_tags = array('meta','img','br','link','area','input','hr','col','param','base');
		preg_match_all('~<([a-z0-9]+)(?: [^>]*)?(?<![/|/ ])>~iU', $html, $result);
		$openedtags = $result[1];
		preg_match_all('~</([a-z0-9]+)>~iU', $html, $result);
		$closedtags = $result[1];
		$len_opened = count($openedtags);
		$len_closed = count($closedtags);

//		if ($len_closed == $len_opened)
//			return $html;

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

	function close_closed_tags($html)
	{
		$single_tags = array('meta','img','br','link','area','input','hr','col','param','base');
		preg_match_all('~<([a-z0-9]+)(?: [^>]*)?(?<![/|/ ])>~iU', $html, $result);
		$openedtags = $result[1];
		preg_match_all('~</([a-z0-9]+)>~iU', $html, $result);
		$closedtags = $result[1];
		$len_opened = count($openedtags);
		$len_closed = count($closedtags);

//		Нельзя, так как число открытых и закрытых несовпадающих тэгов может быть равно:
//		<b>test</s>
//		if ($len_closed == $len_opened)
//			return $html;

		$openedtags = array_reverse($openedtags);

		for ($i=0; $i < $len_closed; $i++)
		{
			if(in_array($closedtags[$i], $single_tags))
				continue;

			if (FALSE !== ($key = array_search($closedtags[$i], $openedtags)))
				unset($openedtags[$key]);
			else
				$html = '<'.$closedtags[$i].'>' . $html;
		}

		return $html;
	}

	static function __unit_test($suite)
	{
		$suite->assertEquals('<i><b>text</b></i>', blib_html::close_opened_tags('<i><b>text</b>'));
		$suite->assertEquals('<s><b>text</b></s>', blib_html::close_closed_tags('<b>text</b></s>'));
		$suite->assertEquals('<div ><blockquote class="q"> test<hr/> </p ></blockquote>', blib_html::close_tags('<div ><blockquote class="q"> test<hr/> </p ></blockquote>'));
	}
}