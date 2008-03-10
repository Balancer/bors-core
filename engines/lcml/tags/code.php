<?
    function lp_code($txt, $params)
    {
        include_once("funcs/modules/colorer.php");

		$txt = restore_format($txt);

		$txt = html_entity_decode($txt, ENT_NOQUOTES);

        $txt = str_replace("lcml_save_left_bracket", "[", $txt);
        $txt = str_replace("lcml_save_lt", "<", $txt);
        $txt = str_replace("lcml_save_gt", ">", $txt);

       	$txt = colorer($txt, $params['orig']);

        $txt = preg_replace("! +$!", "", $txt);

        if(preg_match("!(Created with colorer.+?Type ')(.+?)(')$!m", $txt, $m))
            $txt = preg_replace("!(Created with colorer.+?Type ')(.+?)(')$!m", "", $txt);

        $txt=preg_replace("!^\n+!","",$txt);
        $txt=preg_replace("!\n+$!","",$txt);

        $txt=split("\n",trim($txt));
        foreach($txt as $s)
            $s=" $s";
        
		$txt=join("\n",$txt);

//		$txt = htmlspecialchars($txt);//save_format($txt);
		$txt = str_replace("\n", "<br />---save_cr---", $txt);

//        $txt = "<table border='0' align='center' width='95%' cellpadding='3' cellspacing='1'><tr><td class='code'><tt>$txt</tt>";
        $txt = "<div class=\"code\"><tt>$txt</tt>";

        if(isset($m[2]))
            $txt.="<div class=\"code_type\">code, type '<b>{$m[2]}</b>'</div>";

		$txt .= "</div>";
//		$txt .= "</td></tr></table>";
        
        $txt = preg_replace("!( {2,})!em","str_repeat('&nbsp;',strlen('$1'))",$txt);
//        $txt=str_replace(" ", "&nbsp", $txt);
//        $txt = str_replace("[","&#91;",$txt);
		
		return save_format($txt);
    }
