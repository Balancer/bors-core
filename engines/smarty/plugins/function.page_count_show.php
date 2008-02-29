<?php
    require_once("config.php");
    require_once('funcs/DataBaseHTS.php');

    function smarty_function_page_create_time($params, &$smarty)
    {
        $hts = &new DataBaseHTS();
        return $hts->get_data($GLOBALS['main_uri'], 'create_time');
    }
