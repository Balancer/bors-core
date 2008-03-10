<?
    function lp_font($txt,$params)
    {
        return "<div style=\"font-family: ".addslashes(trim($params['orig'])).";\">".lcml($txt)."</div>";
    }
?>
