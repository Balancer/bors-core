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
	return '>>>base64-save-format>>>'.chunk_split(base64_encode($txt), 32, '# #').'<<<base64-save-format<<<';
}

function restore_format($txt)
{
	return preg_replace('/>>>base64-save-format>>>(.+)<<<base64-save-format<<</e', "base64_decode(str_replace('# #', '', '$1'))", $txt);
}

function remove_format($txt)
{
	$txt = preg_replace(array("!\s+!","!\n!"),array(" "," "),$txt);
	return $txt;
}
