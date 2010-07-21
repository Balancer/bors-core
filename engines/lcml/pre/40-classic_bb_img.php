<?php
    function lcml_classic_bb_img($txt)
    {
		$txt = preg_replace("!\[img\]\s*(.+?)\s*\[/img\]!is", "<img src=\"$1\" alt=\"\" />", $txt);
		$txt = preg_replace("!\[img=\s*([^]]+?)\s*\]!is", "<img src=\"$1\" alt=\"\" />", $txt);
		
		return $txt;
	}
