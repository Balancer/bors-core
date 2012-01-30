<?php

function lcml_auto_images($txt)
{
	if(lcml_tag_disabled('img'))
		return $txt;

	$size = config('box_sizes', 640);

//	$txt = preg_replace("!\[img\](.+?)\[/img\]!i", "[img=$1]'", $txt);
//	$txt = preg_replace("!\[img\s*src=(.+?\.(jpg|png|gif|jpeg|sjpg))\]!i", "[img=$1]", $txt);
	$txt = preg_replace("!\[https?://([^\|\]\s]+?\.(jpg|png|gif|jpeg|sjpg))\|([^\]]+?)\]!is", "[img=$1 {$size}x{$size} left noflow| $3 ]", $txt);
	$txt = preg_replace("!\[https?://([^\|\]\s]+?\.(jpg|png|gif|jpeg|sjpg))\]!i", "[img=$1 {$size}x{$size} left noflow]", $txt);

	return $txt;
}
