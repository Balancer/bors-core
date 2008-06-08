<?php

require_once('engines/bors.php');

if(config('cache_engine'))
	eval('class Cache extends '.config('cache_engine').'{}');

if(config('user_engine'))
	eval('class bors_user extends '.config('user_engine').'{}');
