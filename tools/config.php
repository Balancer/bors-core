<?php

@include_once('setup-host.php');

require_once(__DIR__.'/../init.php');
config_set('system.session.skip', true);

@include_once(__DIR__.'/config-host.php');
