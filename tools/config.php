<?php

@include_once('setup-host.php');

require_once(dirname(__FILE__).'/../init.php');
config_set('system.use_sessions', false);

require_once('config-host.php');
