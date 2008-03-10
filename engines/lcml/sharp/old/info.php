<?
    function lst_info($txt) 
    { 

        list($n, $dtm, $login, $subj, $br, $text) = split("\|", $txt."|||||");
		$user_nick=user_data($login, "nick", $login);
        
        if(!preg_match("!\d\d.\d\d.\d\d\d\d \d\d:\d\d!", $dtm))
            $dtm=strftime("%Y.%m.%d %H:%M",$dtm);

        if($subj)   
            $subj="<caption>$subj</caption>";

        return "<table class=\"btab\" cellSpacing=\"0\" width=\"100%\">$subj<tr><td>$text<div align=\"right\"><small>$dtm, $user_nick</small></div></td></tr></table>\n";
    }
?>
