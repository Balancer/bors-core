---[ Timed tasks ]---
<?php

require_once('../config.php');

require_once(BORS_CORE.'/config.php');
bors_init();

$start = time();
$error = intval(bors_tools_tasks::execute_task());
echo "In ".(time()-$start)." sec<br/>\n";
exit($error);
