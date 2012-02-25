<?php

/**
То же, что и модификатор sklon, но пишет само число.

<p>Всего {$amount|sklonn:'штука,штуки,штук'}
на общую сумму <b>{$sum_rur} руб.</b></p>
*/

function smarty_modifier_sklonn($n, $s1, $s2=NULL, $s5=NULL)
{
    return sklonn($n, $s1, $s2, $s5);
}
