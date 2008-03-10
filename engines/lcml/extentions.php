<?
    function ext_load($dir, $txt=NULL, $mask=false)
    {
//        echo "ext load dir $dir<br>\n";
//		return "Load dir: '$dir'; $txt";

//		if($mask && strlen($txt) != strlen($mask))
//			echo "mask size not match:\n".str_replace("\n","|",$txt)."\n".str_replace("\n","|",$mask)."\n";
        
        if(!is_dir($dir))
			return $txt;
        
        $files = array();

        if($dh = opendir($dir)) 
        {
            while(($file = readdir($dh)) !== false)
                if(!preg_match("!^\.!", $file))
                    $files[] = $file;
        }
        closedir($dh);
        
        sort($files);

		$functions = array();

        foreach($files as $file) 
        {
//            echo "load $file<br>\n";

            if(preg_match("!(.+)\.php$!", $file, $m))
            {
                include_once("$dir/$file");

                $fn = "lcml_".substr($file, 3, -4);
                
                if(function_exists($fn))
					$functions[] = $fn;
            }
//            else
//                ext_load("$dir/$file");

        }

		if(!$mask)
			return lcml_functions_do($functions, $txt);

//		echo "Use mask post:\n$txt\n$mask\n\n";
		
		$result = "";
		$start = 0;
		$can_modif = true;
			
		for($i=0, $stop=strlen($txt); $i<$stop; $i++)
		{
			if($mask{$i} == 'X')
			{
				if($can_modif)
				{
					if($start != $i)
						$result .= lcml_functions_do($functions, substr($txt, $start, $i-$start));
						
					$start = $i;
					$can_modif = false;
				}
			}
			else
			{
				if(!$can_modif)
				{
//					echo "Skip for '".substr($txt, $start, $i-$start)."'\n";
					$result .= substr($txt, $start, $i-$start);
					$start = $i;
					$can_modif = true;
				}
			}
		}


		if($start < strlen($txt))
		{
//			echo "Rest= $start, ".strlen($txt).", '$result:$txt'='".substr($txt, $start, strlen($txt) - $start)."'\n";
			if($can_modif)
				$result .= lcml_functions_do($functions, substr($txt, $start, strlen($txt) - $start));
			else				
				$result .= substr($txt, $start, strlen($txt) - $start);
		}
			
        return $result;
    }

	function lcml_functions_do($functions, $txt)
	{
//		echo "Apply funcs for '$txt'\n";
		foreach($functions as $fn)
		{
			$out = $txt;
			$txt = $fn($txt);
			if(!$txt && $out)
				echo "Drop on $fn convert '".substr($out,0,256)."...'";
		}

		return $txt;
	}
