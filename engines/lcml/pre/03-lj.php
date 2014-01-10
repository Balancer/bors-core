<?php
    function lcml_lj($txt)
    {
		$txt = preg_replace_callback("!(</?lj\-cut[^>]*>)!is", function($m) { return save_format($m[1]);}, $txt);
		return $txt;
	}
