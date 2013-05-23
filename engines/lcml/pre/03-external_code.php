<?php

function lcml_external_code($text)
{
	// YouTube код и ссылки
	if(!config('lcml_external_parse_youtube_disable') && 0)
	{
		$text = preg_replace('!<object[^>]+><param[^>]+value="http://www\.youtube\.com/v/([^&?%]+).*?</object>!s', "\n[youtube]$1[/youtube]\n", $text);
		$text = preg_replace('!(^|\s)http://youtu\.be/([^&/%]+?)(\s|$)!m', "\n[youtube]$2[/youtube]\n", $text);
	}

	$text = preg_replace('!(^|\s)http://rutube\.ru/tracks/\d+\.html\?v=(\w+)(\s|$)!m', "\n[rutube]$2[/rutube]\n", $text);
	$text = preg_replace('!(^|\s)(http://rutube\.ru/tracks/(\d+)\.html\?kot=\d)(\s|$)!m', "\n[rutube original_url=\"$2\"]$3[/rutube]\n", $text);

	$text = preg_replace('!(^|\s)http://prostopleer.com/tracks/(\w+)(\s|$)!m', "\n[prostopleer]$2[/prostopleer]\n", $text);

	// PicasaWeb
	// http://picasaweb.google.com/lh/photo/Ds6wIz_ClELVCBg84Q7-6Q?feat=directlink
	$text = preg_replace('!(^|\s)https?://picasaweb.google.(com|ru)/lh/photo/([\w\-]+)\?feat=directlink($|\s)!m', "\n[picasa]$3[/picasa]\n", $text);
	$text = preg_replace('!(^|\s)https?://picasaweb.google.(com|ru)/lh/photo/([\w\-]+)(\s+|$)!m', "\n[picasa]$3[/picasa]\n", $text);

	// <a href="https://picasaweb.google.com/lh/photo/0RBixxRnZx3VXsMypj5aKtMTjNZETYmyPJy0liipFm0?feat=embedwebsite"><img src="https://lh4.googleusercontent.com/-DG6xaJKJDLw/UVH3li7AJiI/AAAAAAAAGKo/8txakrWsyRY/s640/DSC09354.JPG" height="427" width="640" /></a>
	// http://www.balancer.ru/g/p3145854
	$text = preg_replace('!\s*<a href="https?://picasaweb.google.(com|ru)/lh/photo/([\w\-]+)\?feat=\w+"><img src="[^>]+></a>\s*!s', "\n[picasa]$2[/picasa]\n", $text);

	// pics.livejournal.com
	$text = preg_replace("!((^|\s|\n)http://pics\.livejournal\.com/(\w+)/pic/(\w+)(\s|\n|$))!m", "\n[img $1]\n", $text);
	// http://pics.livejournal.com/uacrussia/pic/0000eae9/s640x480
	$text = preg_replace("!^\s*(http://pics\.livejournal\.com/\w+/pic/\w+)/s\d+x\d+\s*$!m", "\n[img]$1[/img]\n", $text);

	// http://r-img.fotki.yandex.ru/get/5300/alex-hedin.86/0_575e1_d75048a8_orig
	// http://img-fotki.yandex.ru/get/4400/alex-hedin.86/0_575dc_805f7c4e_orig
	// http://img-fotki.yandex.ru/get/5004/balancer73.f/0_4cc96_94922bd7_XL
//	$text = preg_replace("!((^|\s|\n)http://[^/]+fotki\.yandex\.ru/get/\d+/[^/]+/\w+_(orig|XL)(\s|\n|$))!m", "\n[img $1]\n", $text);

	// http://img-fotki.yandex.ru/get/6308/138238612.af/0_77559_be8c8e97_orig
	// http://balancer.ru/g/p2826100
	$text = preg_replace("!^\s*(http://img-fotki\.yandex\.ru/get/\d+/[^/]+/\w+_(orig|XL))\s*$!m", "\n[img]$1.jpg[/img]\n", $text);


	$text = preg_replace('!(<script type="text/javascript" src="http://googlepage.googlepages.com/player.js"></script>)!ise', 'save_format("\1")', $text);

	return $text;
}
