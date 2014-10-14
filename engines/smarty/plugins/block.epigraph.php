<?php

function smarty_block_epigraph($params, $content, &$smarty)
{
    if(!$content)
    	return;

	$size = defval($params, 'size', 1/3);
	$layout = $smarty->getVariable('view')->value->get('layout');
	echo $layout->mod('epigraph', compact('content', 'size'));
}
