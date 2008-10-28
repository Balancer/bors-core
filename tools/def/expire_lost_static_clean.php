<?php

define('BORS_CORE', '/home/balancer/work/programming/php/bors-core');
define('BORS_LOCAL', '/home/balancer/work/programming/php/bors-aviaport-hg/');
	
require_once(BORS_CORE.'/config.php');
require_once('inc/filesystem.php');

define('WORK_DIR', '/mnt/sshfs/www.aviaport.ru/www/sites/aviaport');

main();
bors_exit();

function main()
{
	find_files_loop(WORK_DIR, '.*\.html$', 'do_clean');
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
