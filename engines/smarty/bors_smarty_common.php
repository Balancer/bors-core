<?php

function smarty_template($template_name)
{
	if(!$template_name)
		$template_name = 'default';
	
	if(preg_match("!^\w+$!", $template_name))
		$template_name .= "/index.html";

	if(preg_match('!^xfile://!', $template_name))
		return $template_name;
		
	if($template_name{0} == '/')
		return "xfile:".$template_name;


	foreach(bors_dirs() as $dir)
	{
		if(file_exists($file = $dir.'/templates/'.$template_name))
			return 'xfile:'.$file;

		if(file_exists($file = $dir.'/templates/'.$template_name.'/index.html'))
			return 'xfile:'.$file;
	}
		
	return config('default_template', BORS_CORE.'/templates/default/index.html');
}
