<?php

$w2 = round($size*100);
$w1 = 100-$w2;

echo <<< __EOT__
<table width="100%">
<tr>
	<td width="{$w1}%"></td>
	<td width="{$w2}%">{$content}</td>
</tr>
</table>
<br/>
__EOT__;
