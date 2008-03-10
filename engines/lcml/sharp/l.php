<?
    function lsp_l($txt) 
    { 
        $txt=preg_replace("!^\-\s+!m","\n<li>",$txt);
        return "<ul>\n\n$txt\n</ul>";
    }
?>