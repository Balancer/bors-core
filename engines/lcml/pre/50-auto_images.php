<?php

function lcml_auto_images($txt)
{
	if(lcml_tag_disabled('img'))
		return $txt;

	$size = config('box_sizes', 640);
	if(is_numeric($size))
		$size = "{$size}x{$size}";

//	$txt = preg_replace("!\[img\](.+?)\[/img\]!i", "[img=$1]'", $txt);
//	$txt = preg_replace("!\[img\s*src=(.+?\.(jpg|png|gif|jpeg|sjpg))\]!i", "[img=$1]", $txt);
	$txt = preg_replace("!\[(https?://([^\|\]\s]+?\.(jpg|png|gif|jpeg|sjpg)))\|([^\]]+?)\]!is", "[img=$1 {$size}]", $txt);
	$txt = preg_replace("!\[(https?://([^\|\]\s]+?\.(jpg|png|gif|jpeg|sjpg)))\]!i", "[img=$1 {$size}]", $txt);

	return $txt;
}
