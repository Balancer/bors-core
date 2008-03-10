<?
    function lp_tt($text)
    {
        $text = preg_replace("! !","&nbsp;",$text);
        return "<tt><span style=\"font-family:Courier New;\">".lcml($text)."</span></tt>\n";
    }
?>
