<?php

function lcml_classic_bb_img($txt)
{
	// простые .jpg отрабатываем с утягиванием:
	$txt = preg_replace("!\[img\]([^\[&\?]+\.jpe?g)\[/img\]!is", "[img $1]", $txt);

	$txt = preg_replace("!\[img\]\s*(.+?)\s*\[/img\]!is", "<img src=\"$1\" alt=\"\" />", $txt);
	$txt = preg_replace("!\[img=\s*([^]]+?)\s*\]!is", "<img src=\"$1\" alt=\"\" />", $txt);

	return $txt;
}
