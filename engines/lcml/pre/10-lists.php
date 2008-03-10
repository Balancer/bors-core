<?
    function lcml_lists($txt)
    {
        $txt = split("\n", $txt);

        $sum = array();
        $ul_open = 0;
        $res = '';

		$stack = array();
        foreach($txt as $s)
        {
        	$m = array();
            if(preg_match("!^( +)(\*|#) !m", $s, $m))
            {
                $ident = strlen($m[1]);

                if($ul_open+1 == $ident)
				{
					$ul_open++;
					$tag = $m[2]=='*' ? 'ul' : 'ol';
					$res .= "[$tag]";
					array_push($stack, $tag);
				}
									
                if($ul_open > $ident)
                    for($ul_open; $ul_open>$ident; $ul_open--)
                        $res .= "[/".array_pop($stack)."]";

                $s = @preg_replace("!^ +(\*|#) (.+)$!", "[li]$2[/li]", $s);
                $res .= $s;
            }
            else
            {
                if($res)
                {
                    for($ul_open; $ul_open>0; $ul_open--)
						$res .= "[/".array_pop($stack)."]";
						
                    $sum[] = $res;
                    $res = '';
                }
                $sum[] = $s;
            }
        }

		if($ul_open)
			for($ul_open;$ul_open>0;$ul_open--)
				$res .= "[/".array_pop($stack)."]";

        if($res)
            $sum[] = $res;

//		echo "res=".join("\n", $sum)."\n\n";

        return join("\n", $sum);
    }
