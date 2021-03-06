<?php

function lcml_sharp($txt, &$mask, $lcml)
{
//		if(\B2\Cfg::get('is_developer'))
//			var_dump($txt, restore_format($txt));

        $array = explode("\n", $txt);

		$pos = 0;
		foreach($array as $s)
		{
			$l = bors_strlen($s);
			$m = $mask_array[] = substr($mask, $pos, $l + 1); // Маска на 1 длиннее строки, т.к. запоминается старое значение переноса.
			$pos += $l + 1;
		}

        $in_pair=0;
        $changed=0;
        $start=-1;
		$tag="";

//		$out = "";

        for($i=0, $count = count($array); $i < $count; $i++)
        {
            $s = @$array[$i];

            if(preg_match("!^#(\w+)(\s*)(.*?)$!" , $s, $m)) // Открывающийся или одиночный тег
            {
//            	var_dump("lsp_$m[1]");
                if(function_exists("lsp_$m[1]"))
                {
//	            	var_dump("found");
                    if(!$in_pair) // новый
                    {
                        $in_pair++;
                        $start = $i;
                        $tag = $m[1];
                        $params = trim($m[3]);
                        continue;
                    }

                    if($in_pair && $tag==$m[1]) // такой же
                    {
                        $in_pair++;
                        continue;
                    }
                }

                if(function_exists("lst_{$m[1]}"))
                {
                    $func = "lst_$m[1]";
                    $array[$i] = $func(trim($m[3]), $lcml);
					$mask_array[$i] = str_repeat('X', bors_strlen($array[$i])+1);
                    $changed = 1;
                    continue;
                }
            }

            if(preg_match("!^#/(\w+)(\s|$)!",$s,$m) && $tag==$m[1]) // Новый открывающийся тег
            {
                $in_pair--;
                if(!$in_pair)
                {
                    $func = "lsp_$tag";

                    $txt = $func(join("\n", array_slice($array,$start+1,$i-$start-1)), $params, $lcml);
                    $txt = explode("\n",$txt);

                    $left       = array_slice($array, 0, $start);
                    $mask_left  = array_slice($mask_array, 0, $start);
                    $right      = array_slice($array, $i+1);
                    $mask_right = array_slice($mask_array, $i+1);

					$mask_txt = array();

					for($j=0, $size=sizeof($txt); $j<$size; $j++)
					{
						$mask_txt[$j] = str_repeat('X', bors_strlen($txt[$j])+1);
					}

//					exit(print_r($mask_txt, true)."s=$start, i=$i\n");

//					echo "<xmp>";
//					print_r(array_merge($left, $txt, $right));
//					print_r(array_merge($mask_left, $mask_txt, $mask_right));
//					echo "</xmp>";

                    $array      = array_merge($left, $txt, $right);
                    $mask_array = array_merge($mask_left, $mask_txt, $mask_right);
					
//					$out .= print_r($array,true);
//					$out .= print_r($mask_array,true);
					
//					$i = sizeof($left) + sizeof($txt);
//					$out .= $i;

//					print_r($array);

//					exit("<xmp>$out</xmp>".sizeof($array));

                    $changed = 1;
                }
            }
        }
        
        $txt  = join("\n", $array);
        $mask = join("",  $mask_array);

		if($changed)
            $txt = lcml_sharp($txt, $mask, $lcml);

/*        if(!isset($GLOBALS['forum_tag_found'] && !$GLOBALS['forum_tag_found']))
            $txt.="\n<?\$id=\"$::page_data{forum_id}\";\$page=\"$::page\";include(\"/home/airbase/html/inc/show/forum-comments.phtml\");?>\n";
*/
        return $txt;
}

function lcml_sharp_getset($txt)
{
        $params=array();
        $key="";
        foreach(explode("\n", $txt) as $s)
        {
            if(preg_match("!^(\w+)=(.+)$!",$s,$m))
            {
                $key=$m[1];
                $params[$key]=$m[2];
            }
            else
            {
                if($key)
                    $params[$key].="\n".$s;
            }
        }
        return $params;
}
