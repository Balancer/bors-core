<?php

class bors_markup_xbb
{
	static function parse($text)
	{
		require_once(config('xbb_include'));
		$xbb = new bbcode($text);
		return $xbb->get_html();
	}
}
