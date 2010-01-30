<?
    include_once('engines/lcml/bcsTable.php');

   // Explode CSV string
   function csv_explode($str, $delim = ',', $qual = "\"")
   {
       $skipchars = array( $qual, "\\" );
       $len = bors_strlen($str);
       $inside = false;
       $word = '';
       for ($i = 0; $i < $len; ++$i) 
       {
           $c=bors_substr($str,$i,1);
           if ($c == $delim && !$inside) 
           {
               $out[] = $word;
               $word = '';
           } 
           else if ($inside && in_array($c, $skipchars) && ($i<$len && bors_substr($str,$i+1,1) == $qual)) 
           {
               $word .= $qual;
               $i++;
           } 
           else if ($c == $qual) 
           {
               $inside = !$inside;
           } 
           else {
               $word .= $c;
           }
       }
       $out[] = $word;
	   
       return $out;
   }

    function lp_csv($txt, $params)
    {
		$lcml_parse_cells = bors_strlen($txt) < 512;
	
        $tab = &new bcsTable();

        if(!empty($params['width']))
            $tab->table_width($params['width']);

		$delim = defval($params, 'delim', ';');

        foreach(explode("\n", $txt) as $s)
        {
            if($s = trim($s))
            {
                foreach(csv_explode($s, $delim) as $d)
                {
                    if(preg_match("!^\*(.+)$!", $d, $m))
                    {
                        $d = trim($m[1]);
                        $tab->setHead();
                    }

                    if(preg_match("!^\|(\d+)(.+)$!", $d, $m))
                    {
                        $d = trim($m[2]);
                        $tab->setColSpan($m[1]);
                    }

                    if(preg_match("!^\[cs=(\d+)\](.+)$!", $d, $m))
                    {
                        $d = trim($m[2]);
                        $tab->setColSpan($m[1]);
                    }

                    if(preg_match("!^\[cs=max\](.+)$!", $d, $m))
                    {
                        $d = trim($m[1]);
                        $tab->setColSpan($tab->cols-1 - $tab->col);
                    }

                    if(preg_match("!^\[rs=(\d+)\](.+)$!", $d, $m))
                    {
                        $d = trim($m[2]);
                        $tab->setRowSpan($m[1]);
                    }

                    if($d == '')
                        $d = '&nbsp;';
					elseif($lcml_parse_cells and !preg_match('!^[\w,\-\+\.]+$!', $d))
						$d = lcml($d);

                    $tab->append($d);
                }
                $tab->new_row();
            }
        }

        return remove_format($tab->get_html());
    }

//    echo lp_csv("*1;2;;0\n5;6;7;8",0);
