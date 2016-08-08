<?php

if(file_exists($f = __DIR__.'/../../../../setup.php'))
	require_once $f;

require_once BORS_CORE.'/init.php';
config_set('system.session.skip', true);

if(file_exists($f = COMPOSER_ROOT.'/config-host.php'))
	require_once $f;

if(file_exists($f = __DIR__.'/config-host.php'))
	include_once($f);
