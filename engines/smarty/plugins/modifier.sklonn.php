<?php

/**
То же, что и модификатор sklon, но пишет само число.

<p>Всего {$amount|sklonn:'штука,штуки,штук'}
на общую сумму <b>{$sum_rur} руб.</b></p>
*/

function smarty_modifier_sklonn($n, $s1, $s2=NULL, $s5=NULL)
{
	if($s2 === NULL)
		list($s1, $s2, $s5) = explode(',', $s1);

    $ns=intval(substr($n,-1));
    $n2=intval(substr($n,-2));

    if($n2>=10 && $n2<=19) return $n.' '.$s5;
    if($ns==1) return $n.' '.$s1;
    if($ns>=2&&$ns<=4) return $n.' '.$s2;

    return $n.' '.$s5;
}
