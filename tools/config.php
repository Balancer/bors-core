<?php

$argv = $_SERVER['argv'];

require_once('config-local.php');
include_once(BORS_CORE.'/init.php');
config_set('system.use_sessions', false);
