<?
    function lp_html($txt,$params)
    {
        if(!check_lcml_access('usehtml'))
            return $txt;

        $txt = save_format($txt);
        return $txt;
    }
?>
