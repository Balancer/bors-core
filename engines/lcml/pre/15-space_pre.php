<?php

// Обработка преформатированного текста в случае, если строка начинается с пробела.

function lcml_space_pre($text)
{
	if(!preg_match('/^ /m', $text))
		return $text;

	$out = array();
	$last_pre = array();
	
//	print_d(explode("\n", $text));
	
	foreach(explode("\n", $text) as $s)
	{
		if(preg_match('/^ +\S+/m', $s) || (preg_match('/^ /m', $s) && $last_pre))
			$last_pre[] = substr($s,1);
		elseif($s == '' && $last_pre)
			$last_pre[] = '';
		else
		{
			if($last_pre)
			{
				$out[] = '[pre]';
				$out = array_merge($out, $last_pre);
				$last_pre = array();
				$out[] = "\n[/pre]";
			}

			$out[] = $s;
		}
	}

	if($last_pre)
	{
		$out[] = '[pre]';
		$out = array_merge($out, $last_pre);
		$out[] = "\n[/pre]";
	}

	return join("\n", $out);
}
