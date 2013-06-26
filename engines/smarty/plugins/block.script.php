<?php

function smarty_block_script($params, $content, &$smarty)
{
	if($content == NULL) // Открытие формы
		return;

	echo "<script type=\"text/javascript\"><!--\n";
	echo $content."\n";
	echo "--></script>\n";
	return;
}
