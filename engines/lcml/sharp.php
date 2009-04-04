<?php

function lcml_sharp($txt, &$mask)
{
        $array = explode("\n", $txt);
		
		foreach($array as $s)
			$mask_array[] = str_repeat('.', strlen($s));
		
        $in_pair=0;
        $changed=0;
        $start=-1;
		$tag="";

//		$out = "";

        for($i=0, $count = count($array); $i < $count; $i++)
        {
            $s = @$array[$i];

//			$out .= "test $i: >=$s<=".print_r($array,true)."|".print_r($mask_array,true);

            if(preg_match("!^#(\w+)(\s*)(.*?)$!" ,$s,$m)) // Открывающийся или одиночный тэг
            {
                if(function_exists("lsp_$m[1]"))
                {
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
                    $array[$i] = $func(trim($m[3]));
					$mask_array[$i] = str_repeat('X', strlen($array[$i]));
                    $changed = 1;
                    continue;
                }
            }

            if(preg_match("!^#/(\w+)(\s|$)!",$s,$m) && $tag==$m[1]) // Новый открывающийся тэг
            {
                $in_pair--;
                if(!$in_pair)
                {
                    $func = "lsp_$tag";
//					echo "start=".($start+1).", len=".($i-$start-1);
					
                    $txt = $func(join("\n",array_slice($array,$start+1,$i-$start-1)),$params);
                    $txt = explode("\n",$txt);

//					print_r($txt);

                    $left       = array_slice($array, 0, $start);
                    $mask_left  = array_slice($mask_array, 0, $start);
                    $right      = array_slice($array, $i+1);
                    $mask_right = array_slice($mask_array, $i+1);

					$mask_txt = array();

					for($j=0, $size=sizeof($txt); $j<$size; $j++)
					{
//						echo " <xmp>=>$txt[$j]<=</xmp> ".strlen($txt[$j]);
						$mask_txt[$j] = str_repeat('X', strlen($txt[$j]));
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
        $mask = join(".",  $mask_array);

//        if($changed)
//		{
//            $txt = lcml_sharp($txt);
//    	}
		
/*        if(!isset($GLOBALS['forum_tag_found'] && !$GLOBALS['forum_tag_found']))
            $txt.="\n<?\$id=\"$::page_data{forum_id}\";\$page=\"$::page\";include(\"/home/airbase/html/inc/show/forum-comments.phtml\");?>\n";
*/        
        return /*"<xmp>$out</xmp>".*/$txt;
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
