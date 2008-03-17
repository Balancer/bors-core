<?php

require_once('engines/bors.php');

eval('class Cache extends '.config('cache_engine').'{}');
