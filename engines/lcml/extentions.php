<?php

    function ext_load($dir, $txt=NULL, $mask=false)
    {
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

//		echo "[$txt] $dir<br/>\n";

        sort($files);

		$functions = array();

        foreach($files as $file) 
        {
//			echo "load $file<br/>\n";

            if(preg_match("!(.+)\.php$!", $file, $m))
            {
                include_once("$dir/$file");

                $fn = "lcml_".substr($file, 3, -4);

                if(function_exists($fn))
					$functions[] = $fn;
            }
//            else
//                ext_load("$dir/$file");

//			echo "After: $txt<br/>\n";
        }

		if(!$mask)
			return lcml_functions_do($functions, $txt);

		if(($mask_len = strlen($mask)) != ($txt_len = bors_strlen($txt)))
		{
			debug_hidden_log('lcml-error', "mask length ($mask_len) != text length ($txt_len) for text '$txt'");
			return lcml_functions_do($functions, $txt);
		}

		$result = "";
		$start = 0;
		$can_modif = true;
			
		for($i=0, $stop=bors_strlen($txt); $i<$stop; $i++)
		{
			if(@$mask[$i] == 'X')
			{
				if($can_modif)
				{
					if($start != $i)
						$result .= lcml_functions_do($functions, bors_substr($txt, $start, $i-$start));
						
					$start = $i;
					$can_modif = false;
				}
			}
			else
			{
				if(!$can_modif)
				{
					$result .= bors_substr($txt, $start, $i-$start);
					$start = $i;
					$can_modif = true;
				}
			}
		}


		if($start < bors_strlen($txt))
		{
			if($can_modif)
				$result .= lcml_functions_do($functions, bors_substr($txt, $start, bors_strlen($txt) - $start));
			else				
				$result .= bors_substr($txt, $start, bors_strlen($txt) - $start);
		}
			
        return $result;
    }

	function lcml_functions_do($functions, $txt)
	{
		foreach($functions as $fn)
		{
			$out = $txt;

			$txt = $fn($txt);

			if(!$txt && $out)
				debug_hidden_message('lcml-error', "Lost text on $fn function. Original: '$out'");
		}

		return $txt;
	}
