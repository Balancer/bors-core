<?php

function lcml_classic_bb_img($txt)
{
	// простые .jpg и .png отрабатываем с утягиванием:
//	$txt = preg_replace("!\[url=([^\]]+)\]\[img\]([^\[&\?]+\.(jpe?g|png))\[/img\]\[/url\]!is", "[img=$2]// $1\n", $txt);
	$txt = preg_replace("!\[img\]((https?|ftp)://[^\[&\?]+\.(jpe?g|png))\[/img\]!is", "[img=$1]", $txt);

	// Утягиваем также ЖЖ-шные картинки
	$txt = preg_replace("!\[img\](http://pics.livejournal.com/.+?\w+)\[/img\]!is", "[img=$1]", $txt);

	$txt = preg_replace("!\[img\]\s*((https?|ftp)://.+?)\s*\[/img\]!is", "<img src=\"$1\" alt=\"\" />", $txt);

	return $txt;
}
