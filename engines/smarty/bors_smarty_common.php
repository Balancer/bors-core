<?php

function smarty_template($template_name, $callers_dir = NULL)
{
	$template_name = trim($template_name);

	if(preg_match('!xfile:!', $template_name))
		return $template_name;

	if(preg_match('!bors:!', $template_name))
		return $template_name;

	if(!$template_name)
		$template_name = 'default';

	if(preg_match("!^\w+$!", $template_name))
		$template_name .= "/index.html";

	if(preg_match('!^/!', $template_name))
		return "xfile:".$template_name;

	if(@file_exists($file = $callers_dir . '/' . $template_name))
		return 'xfile:'.$file;

	foreach(bors_dirs(true) as $dir)
	{
		if(file_exists($file = $dir.'/templates/'.$template_name))
			return 'xfile:'.$file;

		if(file_exists($file = $dir.'/'.$template_name))
			return 'xfile:'.$file;

		if(file_exists($file = $dir.'/templates/'.$template_name.'/index.html'))
			return 'xfile:'.$file;
	}

	return false; // \B2\Cfg::get('default_template', BORS_CORE.'/templates/default/index.html');
}
