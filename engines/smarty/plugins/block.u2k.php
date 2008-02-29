<?php
	function smarty_block_u2k($params, $content, &$smarty)
	{
	    if ($content) 
		{
	        echo iconv('utf-8','koi8-r//translit',$content);
	    }
	}
?>
