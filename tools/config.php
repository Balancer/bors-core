<?php

require_once __DIR__.'/../../../../setup.php';

require_once(__DIR__.'/../init.php');
config_set('system.session.skip', true);

include_once(COMPOSER_ROOT.'/config-host.php');
