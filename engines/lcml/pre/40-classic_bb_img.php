<?
    function lcml_classic_bb_img($txt)
    {
		$txt = preg_replace("!\[img\]\s*(.+?)\s*\[/img\]!is", "<img src=\"$1\" border=\"0\" />", $txt);
		$txt = preg_replace("!\[img=\s*([^]]+?)\s*\]!is", "<img src=\"$1\" border=\"0\" />", $txt);
		
		return $txt;
	}
