<?php
    function lcml_tags($txt, &$mask)
    {
		$taglist = config('lcml_tags_enabled');

        $end = 0;
		$next_end = -1;
		$start = time();
        do
        {
            list($pos, $end, $tag, $func, $params) = find_next_open_tag($txt, $end);
            if($pos === false)
                break;

            // Если нашли тэг и он не закрывающийся
            if($pos !== false && $end && bors_substr($txt, $pos+1, 1) != '/')
            {
                if(empty($GLOBALS['cms']['config']['disable']["lp_$func"]) 
					&& function_exists("lp_$func")
					&& (!$taglist || in_array($func, $taglist))
				)
                {
                    $opened   = 0; // число открытых тэгов данного типа
					$cfunc    = "lp_$func";
                    $next_end = $end;
                    do
                    {
                        // Ищем следующий открывающийся тэг
                        list($next_pos, $next_end, $next_tag, $next_func) = find_next_open_tag($txt, $next_end);
//						echo "$cfunc -> $next_tag:$next_func<br />\n";
						if(!$next_tag)
						{
							$pos++;
							break;
						}

                        // Если он такой же, как наш, то увеличиваем счётчик вложений
                        if($next_func == $func)
                            $opened++;

                        // Если он закрывающийся нашего типа, то...
                        if($next_func == "/$func")
                        {
                            // Если есть вложения - уменьшаем
                            if($opened)
                            {
                                $opened--;
                            }
                            // иначе - вычисляем тэг, заменяя его на новое содержимое
                            else
                            {
                                $part1 = bors_substr($txt, 0, $pos);
								$part2 = bors_substr($txt, $end, $next_pos-$end);
                                $part3 = bors_substr($txt, $next_end);
                                $tag_params = params($params);
                                $part2 = $cfunc($part2, $tag_params);

                                if(!empty($tag_params['skip_around_cr']))
                                {
									$part1 = rtrim($part1);
                                	$part3 = ltrim($part3);
                                	$pos = bors_strlen($part1);
//                                	$next_end = $pos + strlen($part2);
                                }

								$mask = substr($mask, 0, $pos).str_repeat('X',bors_strlen($part2)).substr($mask, $next_end);

                                $txt = $part1.$part2.$part3;
// 				                echo "<xmp>tag=$func,p1='$part1'\np2='$part2'\np3='$part3'\n,end=$end,nextpos=$next_pos</xmp>";
                                $next_pos = false;
                                $pos = bors_strlen($part1.$part2); //с конца изменённого фрагмента
                            }
                        }
                    } while($next_pos !== false);

                    $end  = $pos; // В другой раз проверяем с этого же места
                    continue;
                }

//				echo "lt_$func<br/>\n";
                if(empty($GLOBALS['cms']['config']['disable']["lt_$func"])
					&& function_exists("lt_$func")
					&& (!$taglist || in_array($func, $taglist))
					)
                {
					$func = "lt_$func";

                    if(!empty($outfile))
                    {
                        $fh = fopen($GLOBALS['cms']['base_dir']."/funcs/lcml.log","at");
                        fwrite($fh,"$func(".print_r(params($params), true).")\n----------------\n");
                        fclose($fh);
                    }

                    $part1 = bors_substr($txt, 0, $pos);
                    $part2 = $func(params($params));
                    $part3 = bors_substr($txt, $end);
                    $txt  = $part1.$part2.$part3;
					$mask = substr($mask, 0, $pos).str_repeat('X',bors_strlen($part2)).substr($mask, $end);
                    $end  = bors_strlen($part1.$part2); // В другой раз проверяем с конца изменённого фрагмента
                    continue;
                }

                // Неопределённый тэг - пропускаем
                if($pos !== false)
                    $end = $pos+1;
                else
                    $end = false;
            }

        } while($end !== false && time()-$start<20);

        return $txt;
    }

    function find_next_open_tag($txt, $pos)
    {
//    	echo "find tags in ".substr(str_replace("\n", '\n', $txt), $pos, 80)."\n";

    	$strlen = bors_strlen($txt);
//        while($pos < $strlen && ($pos = next_open_brace($txt, $pos)) !== false)
        while($pos < $strlen && ($pos = bors_strpos($txt, '[', $pos)) !== false && $pos < $strlen-2)
        {
			$pos_open  = bors_strpos($txt, '[', $pos+1); // Следующий открывающийся тэг
//            $pos_open  = next_open_brace ($txt, $pos+1); // Следующий открывающийся тэг
            $pos_close = bors_strpos($txt, ']', $pos+1); // Ближайший закрывающий знак
//            $pos_close = next_close_brace($txt, $pos+1); // Ближайший закрывающий знак
            $in = 0;
            $end = 0;

            while($pos_close !== false && $pos_open !== false)
            {
                //  Закрывающий находится ближе открывающего
                //  никаких особых случаев
				//  xxx [b]...[/b]
				//      ^ ------- pos
				//            ^ - pos_open
				//        ^ ----- pos_close
				//        ^ ----- new end
                if($pos_open > $pos_close && $in==0)
                {
                    $end = $pos_close;
                    break;
                }


                // Закрывающийся имеется ближе открывающегося, но
                // мы уже внутри другого открытого.
                // закрываем его и считаем дальше
				// xxx [url ...|[b]...[/b]] yyy
				//     ^ ------------------- pos
				//                    ^ ---------- pos_open
				//                       ^ -- pos_close
				//                        ^ -- new pos_close
                if($pos_open > $pos_close && $in !=0)
                {
                    $in--;
//                    $pos_close = next_close_brace($txt, $pos_close + 1);
                    $pos_close = bors_strpos($txt, ']', $pos_close + 1);
//					echo "2: new pos_close=$pos_close; in=$in\n";
					continue;
                }

                // Новый тэг открывается раньше, чем закрывается наш
                // Начинаем учёт вложений
				// xxx [url ...|[b]...[/b]] yyy
				//     ^ ------------------- pos
				//              ^ ---------- pos_open
				//                ^ -- pos_close
				//                    ^ - new pos_open
				//                       ^ -- new pos_close
                if($pos_open < $pos_close)
                {
//                    $pos_open  = strpos($txt, '[', $pos_open +1);
                    $pos_open  = next_open_brace ($txt, $pos_open +1);
                    $pos_close = bors_strpos($txt, ']', $pos_close+1);
//                    $pos_close = next_close_brace($txt, $pos_close+1);
//					$in++;
//					echo "3: new in=$in\n"; echopos($pos_open, 'pos_open'); echopos($pos_close, 'pos_close');
                }
            }

            if(!$end)
                $end = $pos_close;

            if(!$end)
                $end = $strlen;

            // Вырезаем целиком найденный тэг, без квадратных скобок
            $tag = bors_substr($txt, $pos+1, $end-$pos-1);

            preg_match("!^([^\s\|]*)\s*(.*?)$!s", $tag, $m); // func, params
			//      $next_pos, $next_end, $next_tag, $next_func
            return array($pos, $end+1, $tag, isset($m[1]) ? strtolower($m[1]) : "" , isset($m[2]) ? $m[2] : "");
        }

        return array(false, false, '', '', '');
    }

	function next_open_brace($txt, $pos)
	{
    	$strlen = bors_strlen($txt);
		while($pos < $strlen)
		{
			$pos = bors_strpos($txt, '[', $pos);
			if($pos === false || $pos > $strlen-3)
				return false;

			if(preg_match("!\w|/!", bors_substr($txt, $pos+1, 1)))
				return $pos;

			$pos++;
		}
		return false;
	}
/*
	function next_close_brace($txt, $pos)
	{
		$pos = strpos($txt, ']', $pos);
		if($pos === false)
			return false;

		if($pos == 0)
			return false;

		return $pos;
	}
*/
    function params($in)
    {
        $params=array();

        if(preg_match("!^(.*?)\|(.*)$!s",$in,$m))
        {
            $in=$m[1];
            $params['description']=$m[2];
        }

        $params['orig']    = trim($in);
        $params['width']   = '';//"100%";
        $params['height']   = '';
        $params['_width']  = '';
//        $params['align']   = "left";
        $params['flow']    = ""; // noflow
        $params['_border'] = "";
        $params['border']  = 1;
        $params['size'] = '';
        $params['nohref'] = false;
        $params['noresize'] = false;
//		$params['page'] = $GLOBALS['lcml'][''];

        foreach(preg_split("![\s\n\t]+!",$in) as $param)
        {
            if(preg_match("!^\d+x\d+$!",$param)) { $params['size']=$param; continue;}
            if(preg_match("!^\d+x$!",$param)) { $params['size']=$param; continue;}
            if(preg_match("!^x\d+$!",$param)) { $params['size']=$param; continue;}
            if(preg_match("!^\d+(%|px)$!",$param)) { $params['width']=$param; continue;}
//            if(preg_match("!^(\d+)px$!",$param, $m)) { $params['width']=$m[1]; continue;}
            if(preg_match("!^(left|right|center)$!",$param)) { $params['align']=$param; continue;}
            if(preg_match("!^(flow|noflow)$!",$param)) { $params['flow']=$param; continue;}
            if(preg_match("!^border$!",$param))   { $params['border']=1; continue;}
            if(preg_match("!^noborder$!",$param)) { $params['border']=0; continue;}
            if(preg_match("!^nohref$!",$param)) { $params['nohref']=true; continue;}
            if(preg_match("!^noresize$!",$param)) { $params['noresize']=true; continue;}
//            if(preg_match("!^(\w+)=\"([^\"]+)\"$!s",$param,$m)) { $params[$m[1]]=$m[2]; continue;}
            if(empty($params['url']))
			{
				if(preg_match("!\"(.+)\"!", $param, $m))
	                $params['url'] = $m[1];
				else
	                $params['url'] = $param;
			}
        }

//		print_d($in);

		if(preg_match_all("!(?<=^|\s)(\w+)=\"([^\"]+)\"(?=\s|$)!ms", $in, $match, PREG_SET_ORDER))
		{
//			print_d($match);
			foreach($match as $m)
				$params[strtolower($m[1])] = $m[2];
		}
		
		if(preg_match_all("!(?<=^|\s)(\w+)='([^']+)'(?=\s|$)!ms", $in, $match, PREG_SET_ORDER))
		{
//			print_d($match);
			foreach($match as $m)
				$params[strtolower($m[1])] = $m[2];
		}
		
		if(preg_match_all("!(?<=^|\s)(\w+)=([^\"'\s]+)(?=\s|$)!ms", $in, $match, PREG_SET_ORDER))
		{
//			print_d($match);
			foreach($match as $m)
				$params[strtolower($m[1])] = preg_replace('!^(/forum/smilies/)!', 'http://airbase.ru$1', $m[2]);
		}
		
        if(empty($params['uri']))
			$params['uri'] = @$params['url'];

//		echo "tag uri = {$params['uri']}<br />";

        if(empty($params['uri']))
			$params['uri'] = @$params['cms']['main_uri'];

		require_once("inc/filesystem.php");
		$params['uri'] = secure_path($params['uri']);

        list($iws, $ihs) = explode("x", $params['size']."x");
        if(!$params['width'] && $iws)
            $params['width'] = $iws + 6;

        if(!$params['height'] && $ihs)
            $params['height'] = $ihs + 6;

        if($params['flow'] == "noflow" && !$params['width'])
            $params['width'] = '100%';

        if(isset($params['width']) && $params['width']) $params['_width']=" width=\"{$params['width']}\"";
        if($params['border']) $params['_border']=" class=\"box\"";
//        if(isset($params['_style']) && $params['_style']) $params['_style']=" style=\"".ltrim($params['_style'])."\"";

//        if(!isset($params['_style']))
//            $params['_style']="";

		$params['xwidth'] = $params['width'] ? "width:{$params['width']};" : "";

        if(!empty($params['align']))
        {
            if($params['align']=='center')
            {
                $params['_align_b']="<div {$params['_border']} style=\"text-align: left;\"><table{$params['_width']} cellPadding=\"0\" cellSpacing=\"0\"><tr><td style=\"text-align: justify;\">"; // {$params['_border']}{$params['_style']}
                $params['_align_e']="</td></tr></table></div>";
            }
            else // right or left
            {
                if(empty($params['flow']) || $params['flow'] == 'flow') // С обтеканием текста
                {
//                    $params['_align_b']="<table{$params['_width']} cellPadding=\"0\" cellSpacing=\"0\" align=\"{$params['align']}\"><tr><td>"; // {$params['_border']}{$params['_style']}
//                    $params['_align_e']="</td></tr></table>";
                    $params['_align_b']="<div{$params['_border']} style=\"{$params['xwidth']} float: {$params['align']}; margin-left: 10px; margin-right: 10px;\">"; // {$params['_style']}
                    $params['_align_e']="</div>";
                }
                else
                {
                    $params['_align_b']="<div{$params['_border']} style=\"{$params['xwidth']} margin-left: 10px; margin-right: 10px;\">"; // {$params['_style']}
                    $params['_align_e']="</div>";
//                    $params['_align_b']="<table cellPadding=\"0\" cellSpacing=\"0\"><tr><td{$params['_width']} align=\"{$params['align']}\">"; //{$params['_style']}
//                    $params['_align_e']="</td></tr></table>";
                }
            }
        }

        return $params;
    }

function make_enabled_params($params, $names_list, $skip_list = '')
{
	$res = array();
	foreach(explode(' ', $names_list) as $name)
	{
		if(isset($params[$name]))
			$res[] = "$name=\"".$params[$name]."\"";

		unset($params[$name]);
	}

	if($params)
	{
		$skip_list = " {$skip_list} alt orig _border url uri border width xwidth _width ";
		$att = array();
		foreach($params as $key => $value)
			if($value && strpos($skip_list, " $key ")===false)
				$att[] = "params['$key']='$value'\n";

		if($att)
			debug_hidden_log('lcml-need-attention', "Unknown parameters: ".join(' ', $att));
	}

	return join(' ', $res);
}
