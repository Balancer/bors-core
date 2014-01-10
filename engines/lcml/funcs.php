<?php

function c_type($txt)
{
	if(preg_match("/^[0-9]$/",$txt)) return 1;
	if(preg_match("/^[A-Za-z]$/",$txt)) return 2;
	if(preg_match("/^[а-яА-Я]$/u",$txt)) return 3;
	return 0;
}

function check_lcml_access($var, $default=false)
{
	return $default;
}

function save_format($txt)
{
	return 'lllbase64_save_formatlll'.chunk_split(base64_encode($txt), 32, '# #').'rrrbase64_save_formatrrr';
}

function restore_format($txt)
{
	$loop = 0;
	while(strpos($txt, 'lllbase64_save_formatlll') !== false && strpos($txt, 'rrrbase64_save_formatrrr') !== false && ++$loop < 10)
		$txt = preg_replace_callback('/lllbase64_save_formatlll(.*?)rrrbase64_save_formatrrr/', function($m) {
			return base64_decode(str_replace('# #', '', $m[1]));
		}, $txt);

	if($loop>=10)
		bors_debug::syslog('lcml-warnings', "Too deep restore format. Rest=".$txt);

	return $txt;
}

function remove_format($txt)
{
	$txt = preg_replace(array("!\s+!","!\n!"),array(" "," "),$txt);
	return $txt;
}
