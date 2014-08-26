<?php
function lcml_heads($txt)
{ 
	if(config('lcml_old_exclamation_heads') && preg_match('/^!/', $txt))
		$txt = preg_replace_callback("/^(!{1,6})(.+)$/m", function($m) {
			$n = strlen($m[1]);
			return "<h{$n}>".lcml($m[2])."</h{$n}> ";
		}, $txt);

//	echo "<xmp>"; echo $txt; echo "</xmp>"; exit();
	return $txt;
}
