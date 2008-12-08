---[ Timed tasks ]---
<?php

define('BORS_CORE', dirname(dirname(dirname(__FILE__))));
define('BORS_LOCAL', dirname(BORS_CORE).'/bors-local');

include_once(BORS_CORE.'/config.php');
bors_init();

$start = time();
$error = intval(bors_tools_tasks::execute_task());
echo "In ".(time()-$start)." sec<br/>\n";
exit($error);
