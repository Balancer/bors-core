<?php
    require_once("config.php");

    function smarty_function_page_create_time($params, &$smarty)
    {
        $hts = new DataBaseHTS();
        return $hts->get_data($GLOBALS['main_uri'], 'create_time');
    }
