<?php

function lcml_quote($txt)
{
	//TODO: оптимизировать!
   	if(!config('lcml_balancer'))
   		return $txt;

	$txt = preg_replace('!^\s*={10,}\s*$!m',  '<hr style="height:3px; width:98%;text-align: left;" />', $txt);
	$txt = preg_replace('!^\s*\-{10,}\s*$!m', '<hr style="height:1px; width:98%;text-align: left;" />', $txt);

	$res = array();
	foreach(explode("\n", $txt) as $s)
	{
		$break = false;

		if(bors_strlen($s) > 255)
		{
			foreach(explode(' ', $s) as $tmp)
				if(bors_strlen($tmp) > 255)
				{
					$res[] = "$s";
					$break = true;
					break;
				}
		}

		if($break)
			continue;

		$s = preg_replace("!^(\s*)([^\s><;]+?)(&gt;|>)(.+?)$!", "$1<span class=\"q\"><b>$2</b>&gt;$4</span>", $s);
		$s = preg_replace("!^(\s*)(&gt;|>)(.+?)$!", "$1<span class=\"q\">&gt;$3</span>", $s);
		$res[] = "$s";
	}

	return join("\n", $res);
}
