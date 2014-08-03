<?php

require_once('config.php');

bors_tools_cutycapt::make('http://www.ru', array(
	'out' => __DIR__.'/www.png',
));
