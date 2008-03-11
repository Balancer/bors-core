<?php
    function smarty_function_page_body($params, &$smarty)
    {
        $hts = &new DataBaseHTS();
        return $hts->get_data($GLOBALS['main_uri'], 'body');
    }
