<?php

require_once('engines/smarty/smarty3-resource-file.php');
$smarty->registerResource("xfile", array("smarty_resource_file_get_template",
	"smarty_resource_file_get_timestamp",
	"smarty_resource_file_get_secure",
	"smarty_resource_file_get_trusted"
));

require_once('engines/smarty/smarty-resource-bors.php');
$smarty->registerResource("bors", array("smarty_bors_get_template",
	"smarty_bors_get_timestamp",
	"smarty_bors_get_secure",
	"smarty_bors_get_trusted"
));
