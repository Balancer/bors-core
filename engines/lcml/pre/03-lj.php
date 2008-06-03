<?
    function lcml_lj($txt)
    {
		$txt = preg_replace("!(<lj\-cut[^>]*>)!ise", 'save_format("\1")', $txt);
		
		return $txt;
	}
