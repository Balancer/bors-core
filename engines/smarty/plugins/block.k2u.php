<?php
	function smarty_block_k2u($params, $content, &$smarty)
	{
	    if ($content) 
		{
	        echo iconv('koi8-r','utf-8',$content);
	    }
	}
?>
