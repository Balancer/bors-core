<?
    function lst_c($txt) { return "<h2>".lcml($txt)."</h2>\n";}
    function lst_n($txt) { return "\n<p/>".lcml($txt)."\n";}
    function lst_i($txt) { return "<li/>".lcml($txt)."\n";}
    function lst_ig($txt){ return "<li/>".lcml($txt)."\n";}
    function lst_ib($txt) { return "<li/><b>".lcml($txt)."</b>\n";}
    function lst_p($txt) { return "<p/>".lcml($txt)."\n"; }
    function lst_a($txt) { return lcml("[author]{$txt}[/author]")."\n";}
    function lst_hr($txt) { return "<hr/>\n";}
    function lst_t($txt) { return "<p/>".lcml($txt)."\n";}
    function lst_bt($txt) { return "<b>".lcml($txt)."</b>\n";}
?>
