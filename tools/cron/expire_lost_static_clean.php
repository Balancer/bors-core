<?php

require_once('../config.php');
	
require_once(BORS_CORE.'/config.php');
require_once('inc/filesystem.php');

main();
bors_exit();

function main()
{
	foreach(config('cache_stat_dirs') as $dir)
		find_files_loop($dir, '.*\.html$', 'do_clean');
}

function do_clean($file)
{
	if(filemtime($file) > $GLOBALS['now'] - 86400)
		return;
		
	$content = @file_get_contents($file);
	if(!$content)
		return;

	if(!preg_match('!static expire\s*=\s*(.*?)$!m', $content, $m))
		return;

	if(empty($m[1]))
		return;
	
	if(!($t = strtotime($m[1])))
		return;
	
	if($t+600 > $GLOBALS['now'])
		return;

	debug_hidden_log('static-clean', "{$m[1]}: {$file}", false);
	@unlink($file);
	@rmdir(dirname($file));
	@rmdir(dirname(dirname($file)));
	@rmdir(dirname(dirname(dirname($file))));
	if(file_exists($file))
		debug_hidden_log('static-clean', "Can't remove {$file}", false);
}
