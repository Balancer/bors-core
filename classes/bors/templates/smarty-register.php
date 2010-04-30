<?
    require_once("smarty-resource-file.php");
    require_once("smarty-resource-bors.php");

    $smarty->register_resource("xfile", array("smarty_resource_file_get_template",
                                       "smarty_resource_file_get_timestamp",
                                       "smarty_resource_file_get_secure",
                                       "smarty_resource_file_get_trusted"));


    $smarty->register_resource("bors", array("smarty_bors_get_template",
                                       "smarty_bors_get_timestamp",
                                       "smarty_bors_get_secure",
                                       "smarty_bors_get_trusted"));
