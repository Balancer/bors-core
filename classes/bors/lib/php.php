<?php

// Расширения PHP-системы

class bors_lib_php
{
	function add_include_path($dir) { ini_set('include_path', ini_get('include_path') . PATH_SEPARATOR . $dir); }
}
