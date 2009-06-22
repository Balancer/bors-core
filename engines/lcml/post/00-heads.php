<?php
function lcml_heads($txt)
{ 
	$txt = preg_replace("/=== cut ===/","<table class=\"null w100p\"><tr><td width=\"20\"><hr/></td><td width=\"10\">✂</td><td><hr/></td></tr></table>", $txt);

	if(config('lcml_old_exclamation_heads'))
	{
		$txt=preg_replace("/^!!!!!(.+)$/me","'<h6>'.lcml(\"$1\").'</h6>'",$txt);
	    $txt=preg_replace("/^!!!!(.+)$/me","'<h5>'.lcml(\"$1\").'</h5>'",$txt);
        $txt=preg_replace("/^!!!(.+)$/me","'<h4>'.lcml(\"$1\").'</h4>'",$txt);
	    $txt=preg_replace("/^!!(.+)$/me","'<h3>'.lcml(\"$1\").'</h3>'",$txt);
		$txt=preg_replace("/^!(.+)$/me","'<h2>'.lcml(\"$1\").'</h2>'",$txt);
	}
	
        $txt=preg_replace("/^ *===== (.+) ===== *$/me","'<h6>'.lcml(\"$1\").'</h6>'",$txt);
        $txt=preg_replace("/^ *==== (.+) ==== *$/me","'<h5>'.lcml(\"$1\").'</h5>'",$txt);
        $txt=preg_replace("/^ *=== (.+) === *$/me","'<h4>'.lcml(\"$1\").'</h4>'",$txt);
        $txt=preg_replace("/^ *== (.+) == *$/me","'\n<h3>'.(\"$1\").'</h3>\n'",$txt);
		
//		echo "<xmp>"; echo $txt; echo "</xmp>"; exit();
		
        $txt=preg_replace("/^\s*= (.+) =\s*$/me","'<h2>'.lcml(\"$1\").'</h2>'",$txt);

        return $txt;
}
