<?php

function smarty_block_lcml($params, $content, &$smarty)
{
    if ($content) 
	{
		$save = config('lcml_tags_enabled');
		config_set('lcml_tags_enabled', NULL);
        echo lcml($content, array('html_disable' => false, 'lcml_tags_enabled' => NULL));
		config_set('lcml_tags_enabled', $save);
    }
}
