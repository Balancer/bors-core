<?php
function lcml_heads($txt)
{ 
	if(config('lcml_old_exclamation_heads'))
	{
		$txt=preg_replace("/^!!!!!(.+)$/me","'<h6>'.lcml(\"$1\").'</h6> '",$txt);
	    $txt=preg_replace("/^!!!!(.+)$/me","'<h5>'.lcml(\"$1\").'</h5> '",$txt);
        $txt=preg_replace("/^!!!(.+)$/me","'<h4>'.lcml(\"$1\").'</h4> '",$txt);
	    $txt=preg_replace("/^!!(.+)$/me","'<h3>'.lcml(\"$1\").'</h3> '",$txt);
		$txt=preg_replace("/^!(.+)$/me","'<h2>'.lcml(\"$1\").'</h2> '",$txt);
	}
	
//		echo "<xmp>"; echo $txt; echo "</xmp>"; exit();
        return $txt;
}
