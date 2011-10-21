<?php

@include_once('setup-host.php');

require_once(dirname(__FILE__).'/../init.php');
config_set('system.session.skip', true);

@include_once(dirname(__FILE__).'/config-host.php');

