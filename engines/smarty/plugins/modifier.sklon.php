<?php
function smarty_modifier_sklon($n, $s1, $s2=NULL, $s5=NULL) // 1 нож 2 ножа 5 ножей
{
	if($s2 === NULL)
		list($s1, $s2, $s5) = explode(',', $s1);
			
    $ns=intval(substr($n,-1));
    $n2=intval(substr($n,-2));

    if($n2>=10 && $n2<=19) return $s5;
    if($ns==1) return $s1;
    if($ns>=2&&$ns<=4) return $s2;
    return $s5;
}
