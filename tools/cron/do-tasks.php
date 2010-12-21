<?php

require_once('../config.php');

if(!config('bors_tasks_enabled'))
	return;

echo "---[ Timed tasks ]---\n";

bors_init();

$start = time();
$error = intval(bors_tools_tasks::execute_task());
echo "In ".(time()-$start)." sec<br/>\n";
exit($error);
