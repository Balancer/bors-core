<?
    /*
        Формат пустая строка -> абзац
    */

    function lsp_format($txt) 
    { 
        $txt = preg_replace("!^\s*$!m","\n<p/>",$txt);
        return "<p/>$txt\n";
    }
?>