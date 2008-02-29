<?php
    require_once('funcs/DataBaseHTS.php');

    function smarty_function_page_right_column($params, &$smarty)
    {
        $hts = new DataBaseHTS;
        return $hts->get_data($GLOBALS['main_uri'], 'right_column');
    }
?>
