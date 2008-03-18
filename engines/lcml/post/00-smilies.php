<?
    // Smilies processing
    // Global vars:
    // $GLOBALS['cms_smilies_dir'] - full path to smilies dir
    // $GLOBALS['cms_smilies_url'] - full or relative url of smilies dir
    //
    // (c) Balancer 2003-2004


    function lcml_smilies($txt)
    {
        $smilies = file(config('smilies_dir')."/list.txt");
		
        for($i=0;$i<sizeof($smilies);$i++)
        {
            $spl=split(" ",str_replace("\r","",chop($smilies[$i])));
            $spl[]="";
            list($code,$file)=$spl;
            if(!$file)
            {
                $txt=preg_replace("!([^\"]):$code:([^\"])!","$1<img src=\"".config('smilies_url')."/$code.gif\" alt=\":$code:\" title=\":$code:\" border=\"0\" />$2",$txt);
            }
            else
            {
//                debug("Smile: =$code=$file=$txt=");
                
                $from=array("/\(/","/\)/","/\[/","/\]/","/\-/","/\*/","/\+/","/\./","/\?/","/\|/","/\!/");
                $to=array("\\\(","\\\)","\\\[","\\\]","\\\-","\\\*","\\\+","\\\/","\\\?","\\\|","\\\!");

//                debug("txt=preg_replace(\"!(^|\s)\".preg_replace($code)\"(?=(\s|$|\)|\]|\.))!\",\"1<img src=\"{config('smilies_url')}/$file.gif\" alt=\"$code\" title=\"$code\" border=\"0\" />\",$txt);");

                $txt=preg_replace("!(^|\s)".preg_replace($from,$to,$code)."(?=(\s|$|\)|\]|\.))!us","$1<img src=\"".config('smilies_url')."/$file.gif\" alt=\"$code\" title=\"$code\" border=\"0\" />",$txt);
            }
        }

        $txt = lcml_smilies_by_files(config('smilies_dir'),$txt);

        return $txt;
    }

    function lcml_smilies_by_files($dir,$txt)
    {
		$from = array();
		$to   = array();

        foreach(lcml_smilies_list($dir) as $code)
		{
			$from[] = "![^\"]:$code:!";
			$to[]   = "$1<img src=\"".config('smilies_url')."/$code.gif\" alt=\":$code:\" title=\":$code:\" border=\"0\" />";
		}

        return preg_replace($from, $to, $txt);
    }

    function lcml_smilies_list($dir)
	{
        $cache = &new Cache();

        if($cache->get('smilies-v6', $dir))
//		{
//			if(is_array($cache->last()))
//			{
//				return $cache->last();
//			}
//			else
//			{
//				echolog("Given smilies array ".print_r($cache->last(), true), 1);
	            return $cache->last();
//			}
//		}

		$list = lcml_smilies_load($dir);

		$cache->set($list, 30*86400);
		return $list;
	}
	
    function lcml_smilies_load($dir)
    {
        $list = array();

        if(is_dir($dir))
        {
            if($dh = opendir($dir)) 
            {
                while(($file = readdir($dh)) !== false) 
                {
                    if(substr($file,-4)=='.gif')
                        $list[] = substr($file,0,-4);
                    elseif(filetype("$dir/$file")=='dir' && substr($file,0,1)!='.')
                        $list = array_merge($list, lcml_smilies_load("$dir/$file"));
                }
                closedir($dh);
            }
        }

        return $list;
    }
