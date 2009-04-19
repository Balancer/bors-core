<?php

function lcml_line_format_fix($txt)
{
	if(empty($GLOBALS['lcml']['cr_type']))
		$cr_type = 'empty_as_para';
	else
		$cr_type = $GLOBALS['lcml']['cr_type'];

	if(!trim($txt))
		return $txt;

	switch($cr_type)
	{
		case 'empty_as_para':
			$txt = "<p>{$txt}</p>";
			break;
	}

	return $txt;
}
