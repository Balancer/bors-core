<?php

if(version_compare(PHP_VERSION, '5.3') >= 0)
	eval('class bors_legacy extends bors_legacy_53 { }');
else
	eval('class bors_legacy extends bors_legacy_52 { }');

