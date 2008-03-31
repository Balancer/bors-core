<?php

require_once('engines/bors.php');

@eval('class Cache extends '.config('cache_engine').'{}');
@eval('class bors_user extends '.config('user_engine').'{}');
