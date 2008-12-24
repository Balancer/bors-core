<?php

function bors_thread_lock($section_name, $timeout = 60)
{
	$flock = config('cache_dir')."/$section_name.thread_lock";
	
	if(file_exists($flock) && filemtime($flock) > time() - $timeout)
		return false;
	                
	file_put_contents($flock, 1);

	return true;                    
}

function bors_thread_unlock($section_name)
{
	@unlink(config('cache_dir')."/$section_name.thread_lock");
}
