<?
    function lcml_quote($txt)
    {
		$txt = preg_replace('!^\s*={10,}\s*$!m',  '<hr style="height:3px; width:98%;text-align: left;" />', $txt);
		$txt = preg_replace('!^\s*\-{10,}\s*$!m', '<hr style="height:1px; width:98%;text-align: left;" />', $txt);
		
		$res = "";
		foreach(split("\n", $txt) as $s)
		{
			$break = false;
		
			if(strlen($s) > 255)
			{
				foreach(split(' ', $s) as $tmp)
					if(strlen($tmp) > 255)
					{
						$res .= "$s\n";
						$break = true;
						break;
					}
			}
			
			if($break)
				continue;
			
			$s = preg_replace("!^(\s*)([^\s><]*?)(&gt;|>)(.+?)$!s", "$1<span class=\"q\"><b>$2</b>&gt;$4</span>", $s);
			$res .= "$s\n";
		}
		
        return $res;
    }
