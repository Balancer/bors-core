<?php

require_once('engines/bors.php');

if(config('cache_engine'))
{
	eval('class Cache extends '.config('cache_engine').'{}');
	eval('class bors_cache extends '.config('cache_engine').'{}');
}

if(config('user_class'))
	eval('class bors_user extends '.config('user_class').'{}');
