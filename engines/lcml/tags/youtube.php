<?php

function lp_youtube($id, &$params)
{
	$width  = @$params['width']  ? $params['width']  : '425';
	$height = @$params['height'] ? $params['height'] : '344';

	return "<object width=\"{$width}\" height=\"{$height}\"><param name=\"movie\" value=\"http://www.youtube.com/v/{$id}&hl=ru&fs=1&\"></param><param name=\"allowFullScreen\" value=\"true\"></param><param name=\"allowscriptaccess\" value=\"always\"></param><embed src=\"http://www.youtube.com/v/{$id}&hl=ru&fs=1&\" type=\"application/x-shockwave-flash\" allowscriptaccess=\"always\" allowfullscreen=\"true\" width=\"{$width}\" height=\"{$height}\"></embed></object>\n";
}
