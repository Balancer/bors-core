<?php
function smarty_modifier_colorize($string, $idx, $c1, $c2)
{
	if(!trim($string))
		return '';

	echo "<span style=\"color: ".($idx ? $c2 : $c1)."\">$string</span>";
}
